<?php
namespace CG\Product\Link\Service;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter;
use CG\Product\Link\Mapper;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection as StockLocations;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\Location\TypedEntity as TypedStockLocation;
use CG\Stock\StorageInterface as StockStorage;

class Service extends BaseService
{
    const RECURSION_MSG = 'Circular dependency detected. The product you are trying to link (SKU: %s) is already used to calculate stock for another product that you are trying to link this product to (SKU: %s).';

    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;

    public function __construct(
        StorageInterface $storage,
        Mapper $mapper,
        StockLocationStorage $stockLocationStorage,
        StockStorage $stockStorage,
        NginxCacheInvalidator $nginxCacheInvalidator
    ) {
        parent::__construct($storage, $mapper);
        $this->stockLocationStorage = $stockLocationStorage;
        $this->stockStorage = $stockStorage;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
    }

    public function remove($productLink)
    {
        parent::remove($productLink);
        $this->updateRelatedStockLocationsFromRemove($productLink);
    }

    /**
     * @param ProductLink $productLink
     */
    public function save($productLink)
    {
        try {
            $currentEntity = $this->fetch($productLink->getId());
        } catch (NotFound $exception) {
            $currentEntity = null;
        }

        $this->checkForRecursion($productLink);
        $savedEntity = parent::save($productLink);
        $this->updateRelatedStockLocationsFromSave($savedEntity, $currentEntity);
        return $savedEntity;
    }

    protected function checkForRecursion(ProductLink $productLink, array $productSkuMap = [])
    {
        $productSkuMap[strtolower($productLink->getProductSku())] = true;

        $ouIdProductSku = [];
        foreach (array_keys($productLink->getStockSkuMap()) as $stockSku) {
            $productSku = strtolower($stockSku);
            if (isset($productSkuMap[$productSku])) {
                throw new RecursionException(
                    sprintf(static::RECURSION_MSG, $stockSku, $productLink->getProductSku())
                );
            }
            $productSkuMap[$productSku] = true;
            $ouIdProductSku[] = $productLink->getOrganisationUnitId() . '-' . $stockSku;
        }

        if (empty($ouIdProductSku)) {
            return;
        }

        try {
            $productLinks = $this->fetchCollectionByFilter(
                (new Filter('all', 1))->setOuIdProductSku($ouIdProductSku)
            );
        } catch (NotFound $exception) {
            return;
        }

        /** @var ProductLink $productLink */
        foreach ($productLinks as $productLink) {
            $this->checkForRecursion($productLink, $productSkuMap);
        }
    }

    protected function updateRelatedStockLocationsFromRemove(ProductLink $productLink)
    {
        $this->updateRelatedStockLocations(
            [$this->generateOuIdSkuForProductLink($productLink) => TypedStockLocation::TYPE_REAL]
        );
    }

    protected function updateRelatedStockLocationsFromSave(ProductLink $savedEntity, ProductLink $currentEntity = null)
    {
        $ouIdSkuUpdateMap = [
            ($savedOuIdSku = $this->generateOuIdSkuForProductLink($savedEntity)) => TypedStockLocation::TYPE_LINKED,
        ];

        if ($currentEntity && ($currentOuIdSku = $this->generateOuIdSkuForProductLink($currentEntity)) != $savedOuIdSku) {
            $ouIdSkuUpdateMap[$currentOuIdSku] = TypedStockLocation::TYPE_REAL;
        }

        $this->updateRelatedStockLocations($ouIdSkuUpdateMap);
    }

    protected function updateRelatedStockLocations(array $ouIdSkuUpdateMap)
    {
        try {
            $stockLocations = $this->stockLocationStorage->fetchCollectionByFilter(
                (new StockLocationFilter('all', 1))
                    ->setOuIdSku(array_keys($ouIdSkuUpdateMap))
            );
        } catch (NotFound $exception) {
            // No stock locations to update
            return;
        }

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $ouIdSku = $this->generateOuIdSkuForStockLocation($stockLocation);
            if ($stockLocation instanceof TypedStockLocation && isset($ouIdSkuUpdateMap[$ouIdSku])) {
                $stockLocation->setType($ouIdSkuUpdateMap[$ouIdSku]);
            }
        }

        $updater = new class()
        {
            use LogTrait;
            use SaveCollectionHandleErrorsTrait {
                saveCollectionHandleErrors as public;
            }

            /**
             * @param StockLocation $fetchedEntity
             * @param StockLocation $passedEntity
             */
            protected function reapplyChangesToEntityAfterConflict($fetchedEntity, $passedEntity)
            {
                if ($fetchedEntity instanceof TypedStockLocation && $passedEntity instanceof TypedStockLocation) {
                    $fetchedEntity->setType($passedEntity->getType());
                }
                return $fetchedEntity;
            }
        };

        $stockLocations = $updater->saveCollectionHandleErrors($this->stockLocationStorage, $stockLocations);
        $this->invalidateStockLocations($stockLocations);
    }

    protected function generateOuIdSku($ouId, $sku)
    {
        return $ouId . '-' . strtolower($sku);
    }

    protected function generateOuIdSkuForProductLink(ProductLink $productLink)
    {
        return $this->generateOuIdSku($productLink->getOrganisationUnitId(), $productLink->getProductSku());
    }

    protected function generateOuIdSkuForStockLocation(StockLocation $stockLocation)
    {
        return $this->generateOuIdSku($stockLocation->getOrganisationUnitId(), $stockLocation->getSku());
    }

    protected function invalidateStockLocations(StockLocations $stockLocations)
    {
        try {
            /** @var StockCollection $stockCollection */
            $stockCollection = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($stockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            // No related stock entities - can't invalidate nginx cache
            return;
        }

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stockCollection->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }
            $this->nginxCacheInvalidator->invalidateProductsForStockLocation(
                $stockLocation,
                $stock
            );
        }
    }
}