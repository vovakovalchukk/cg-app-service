<?php
namespace CG\Product\Link\Service;

use CG\CGLib\Nginx\Cache\Invalidator\ProductLink as ProductLinkNginxCacheInvalidator;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as ProductStockNginxCacheInvalidator;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Mapper;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\StorageInterface;
use CG\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorage;
use CG\Product\LinkNode\Entity as ProductLinkNode;
use CG\Product\LinkNode\StorageInterface as ProductLinkNodeStorage;
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
    /** @var ProductLinkLeafStorage $productLinkLeafStorage */
    protected $productLinkLeafStorage;
    /** @var ProductLinkNodeStorage $productLinkNodeStorage */
    protected $productLinkNodeStorage;
    /** @var ProductLinkNginxCacheInvalidator $productLinkNginxCacheInvalidator */
    protected $productLinkNginxCacheInvalidator;
    /** @var StockLocationStorage $stockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductStockNginxCacheInvalidator $productStockNginxCacheInvalidator */
    protected $productStockNginxCacheInvalidator;

    public function __construct(
        StorageInterface $storage,
        Mapper $mapper,
        ProductLinkLeafStorage $productLinkLeafStorage,
        ProductLinkNodeStorage $productLinkNodeStorage,
        ProductLinkNginxCacheInvalidator $productLinkNginxCacheInvalidator,
        StockLocationStorage $stockLocationStorage,
        StockStorage $stockStorage,
        ProductStockNginxCacheInvalidator $productStockNginxCacheInvalidator
    ) {
        parent::__construct($storage, $mapper);
        $this->productLinkLeafStorage = $productLinkLeafStorage;
        $this->productLinkNodeStorage = $productLinkNodeStorage;
        $this->productLinkNginxCacheInvalidator = $productLinkNginxCacheInvalidator;
        $this->stockLocationStorage = $stockLocationStorage;
        $this->stockStorage = $stockStorage;
        $this->productStockNginxCacheInvalidator = $productStockNginxCacheInvalidator;
    }

    public function remove($productLink)
    {
        $this->invalidateRelatedProductLink($productLink);
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
            $this->invalidateRelatedProductLink($currentEntity);
        } catch (NotFound $exception) {
            $currentEntity = null;
        }

        $savedEntity = parent::save($productLink);
        $this->invalidateRelatedProductLink($savedEntity);
        $this->updateRelatedStockLocationsFromSave($savedEntity, $currentEntity);
        return $savedEntity;
    }

    protected function invalidateRelatedProductLink(ProductLink $productLink)
    {
        try {
            /** @var ProductLinkNode $productLinkNode */
            $productLinkNode = $this->productLinkNodeStorage->fetch(
                $this->generateOuIdSkuForProductLink($productLink)
            );
        } catch (NotFound $exception) {
            // No related graphs to invalidate
            return;
        }

        $this->invalidateProductLink(
            $this->generateOuIdSkuForProductLink($productLink)
        );

        foreach ($productLinkNode as $sku) {
            $this->invalidateProductLink(
                ProductLink::generateId($productLinkNode->getOrganisationUnitId(), $sku)
            );
        }
    }

    protected function invalidateProductLink($id)
    {
        $this->productLinkLeafStorage->invalidate($id);
        $this->productLinkNodeStorage->invalidate($id);
        $this->productLinkNginxCacheInvalidator->invalidateRelated($id);
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

    protected function generateOuIdSkuForProductLink(ProductLink $productLink)
    {
        return ProductLink::generateId($productLink->getOrganisationUnitId(), $productLink->getProductSku());
    }

    protected function generateOuIdSkuForStockLocation(StockLocation $stockLocation)
    {
        return ProductLink::generateId($stockLocation->getOrganisationUnitId(), $stockLocation->getSku());
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
            $this->productStockNginxCacheInvalidator->invalidateProductsForStockLocation(
                $stockLocation,
                $stock
            );
        }
    }
}