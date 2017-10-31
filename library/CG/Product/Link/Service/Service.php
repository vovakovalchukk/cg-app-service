<?php
namespace CG\Product\Link\Service;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Product\Graph\Entity as ProductGraph;
use CG\Product\Graph\StorageInterface as ProductGraphStorage;
use CG\Product\Link\Entity as ProductLink;
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
    /** @var ProductGraphStorage $productGraphStorage */
    protected $productGraphStorage;
    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;

    public function __construct(
        StorageInterface $storage,
        Mapper $mapper,
        ProductGraphStorage $productGraphStorage,
        StockLocationStorage $stockLocationStorage,
        StockStorage $stockStorage,
        NginxCacheInvalidator $nginxCacheInvalidator
    ) {
        parent::__construct($storage, $mapper);
        $this->productGraphStorage = $productGraphStorage;
        $this->stockLocationStorage = $stockLocationStorage;
        $this->stockStorage = $stockStorage;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
    }

    public function remove($productLink)
    {
        $this->invalidateProductGraphs($productLink);
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
            $this->invalidateProductGraphs($currentEntity);
        } catch (NotFound $exception) {
            $currentEntity = null;
        }

        $savedEntity = parent::save($productLink);
        $this->invalidateProductGraphs($savedEntity);
        $this->updateRelatedStockLocationsFromSave($savedEntity, $currentEntity);
        return $savedEntity;
    }

    protected function invalidateProductGraphs(ProductLink $productLink)
    {
        try {
            /** @var ProductGraph $productGraph */
            $productGraph = $this->productGraphStorage->fetch(
                $this->generateOuIdSkuForProductLink($productLink)
            );
        } catch (NotFound $exception) {
            // No related graphs to invalidate
            return;
        }

        foreach ($productGraph as $sku => $quantity) {
            $this->productGraphStorage->invalidate(
                $this->generateOuIdSku($productGraph->getOrganisationUnitId(), $sku)
            );
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