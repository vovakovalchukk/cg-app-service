<?php
namespace CG\Product\Link\Service;

use CG\CGLib\Nginx\Cache\Invalidator\ProductLink as ProductLinkNginxCacheInvalidator;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as ProductStockNginxCacheInvalidator;
use CG\Http\SaveCollectionHandleErrorsTrait;
use CG\Order\Client\Gearman\Generator\AllocatedStockCorrection as AllocatedStockCorrectionGearmanJobGenerator;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Mapper;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\StorageInterface;
use CG\Product\LinkLeaf\StorageInterface as ProductLinkLeafStorage;
use CG\Product\LinkNode\StorageInterface as ProductLinkNodeStorage;
use CG\Product\LinkPaths\StorageInterface as ProductLinkPathsStorage;
use CG\Product\LinkRelated\Entity as ProductLinkRelated;
use CG\Product\LinkRelated\StorageInterface as ProductLinkRelatedStorage;
use CG\Product\Mapper as ProductMapper;
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
    /** @var ProductLinkLeafStorage */
    protected $productLinkLeafStorage;
    /** @var ProductLinkNodeStorage */
    protected $productLinkNodeStorage;
    /** @var ProductLinkRelatedStorage */
    protected $productLinkRelatedStorage;
    /** @var ProductLinkPathsStorage */
    protected $productLinkPathsStorage;
    /** @var ProductLinkNginxCacheInvalidator */
    protected $productLinkNginxCacheInvalidator;
    /** @var AllocatedStockCorrectionGearmanJobGenerator */
    protected $allocatedStockCorrectionGearmanJobGenerator;
    /** @var StockLocationStorage */
    protected $stockLocationStorage;
    /** @var StockStorage */
    protected $stockStorage;
    /** @var ProductStockNginxCacheInvalidator */
    protected $productStockNginxCacheInvalidator;

    public function __construct(
        StorageInterface $storage,
        Mapper $mapper,
        ProductMapper $productMapper,
        ProductLinkLeafStorage $productLinkLeafStorage,
        ProductLinkNodeStorage $productLinkNodeStorage,
        ProductLinkRelatedStorage $productLinkRelatedStorage,
        ProductLinkPathsStorage $productLinkPathsStorage,
        ProductLinkNginxCacheInvalidator $productLinkNginxCacheInvalidator,
        AllocatedStockCorrectionGearmanJobGenerator $allocatedStockCorrectionGearmanJobGenerator,
        StockLocationStorage $stockLocationStorage,
        StockStorage $stockStorage,
        ProductStockNginxCacheInvalidator $productStockNginxCacheInvalidator
    ) {
        parent::__construct($storage, $mapper, $productMapper);
        $this->productLinkLeafStorage = $productLinkLeafStorage;
        $this->productLinkNodeStorage = $productLinkNodeStorage;
        $this->productLinkRelatedStorage = $productLinkRelatedStorage;
        $this->productLinkPathsStorage = $productLinkPathsStorage;
        $this->productLinkNginxCacheInvalidator = $productLinkNginxCacheInvalidator;
        $this->allocatedStockCorrectionGearmanJobGenerator = $allocatedStockCorrectionGearmanJobGenerator;
        $this->stockLocationStorage = $stockLocationStorage;
        $this->stockStorage = $stockStorage;
        $this->productStockNginxCacheInvalidator = $productStockNginxCacheInvalidator;
    }

    public function remove($productLink)
    {
        $this->invalidateRelatedProductLink($productLink);
        parent::remove($productLink);
        $this->correctAllocatedStockFromRemove($productLink);
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
        $this->correctAllocatedStockFromSave($savedEntity, $currentEntity);
        $this->updateRelatedStockLocationsFromSave($savedEntity, $currentEntity);
        return $savedEntity;
    }

    protected function invalidateRelatedProductLink(ProductLink $productLink)
    {
        foreach ($this->getRelatedProductLinkIds($productLink->getId()) as $relatedProductLinkId) {
            $this->invalidateProductLink($relatedProductLinkId);
        }
    }

    protected function getRelatedProductLinkIds($id): array
    {
        try {
            /** @var ProductLinkRelated $productLinkRelated */
            $productLinkRelated = $this->productLinkRelatedStorage->fetch($id);
            return array_map(function(string $sku) use($productLinkRelated) {
                return ProductLinkRelated::generateId($productLinkRelated->getOrganisationUnitId(), $sku);
            }, $productLinkRelated->getRelatedSkusMap());
        } catch (NotFound $exception) {
            return [$id];
        }
    }

    protected function invalidateProductLink($id)
    {
        $this->productLinkLeafStorage->invalidate($id);
        $this->productLinkNodeStorage->invalidate($id);
        $this->productLinkRelatedStorage->invalidate($id);
        $this->productLinkPathsStorage->invalidate($id);
        $this->productLinkNginxCacheInvalidator->invalidateRelated($id);
    }

    protected function correctAllocatedStockFromRemove(ProductLink $productLink)
    {
        ($this->allocatedStockCorrectionGearmanJobGenerator)(
            $productLink->getOrganisationUnitId(),
            ...array_merge([$productLink->getProductSku()], array_keys($productLink->getStockSkuMap()))
        );
    }

    protected function updateRelatedStockLocationsFromRemove(ProductLink $productLink)
    {
        $this->updateRelatedStockLocations(
            [$this->generateOuIdSkuForProductLink($productLink) => TypedStockLocation::TYPE_REAL]
        );
    }

    protected function correctAllocatedStockFromSave(ProductLink $savedEntity, ProductLink $currentEntity = null)
    {
        if (!$currentEntity) {
            $this->recalculateAllocatedStock($savedEntity);
            return;
        }

        if (
            $currentEntity->getOrganisationUnitId() != $savedEntity->getOrganisationUnitId()
            || $currentEntity->getProductSku() != $savedEntity->getProductSku()
        ) {
            $this->recalculateAllAllocatedStock($savedEntity, $currentEntity);
            return;
        }

        $this->recalculateChangedAllocatedStock($savedEntity, $currentEntity);
    }

    protected function recalculateAllocatedStock(ProductLink $savedEntity)
    {
        ($this->allocatedStockCorrectionGearmanJobGenerator)(
            $savedEntity->getOrganisationUnitId(),
            ...array_keys($savedEntity->getStockSkuMap())
        );
    }

    protected function recalculateAllAllocatedStock(ProductLink $savedEntity, ProductLink $currentEntity)
    {
        ($this->allocatedStockCorrectionGearmanJobGenerator)(
            $currentEntity->getOrganisationUnitId(),
            ...array_merge([$currentEntity->getProductSku()], array_keys($currentEntity->getStockSkuMap()))
        );
        ($this->allocatedStockCorrectionGearmanJobGenerator)(
            $savedEntity->getOrganisationUnitId(),
            ...array_keys($savedEntity->getStockSkuMap())
        );
    }

    protected function recalculateChangedAllocatedStock(ProductLink $savedEntity, ProductLink $currentEntity)
    {
        $skus = [];
        $removedStockSkus = array_diff_ukey($currentEntity->getStockSkuMap(), $savedEntity->getStockSkuMap(), 'strcasecmp');
        foreach (array_keys($removedStockSkus) as $stockSku) {
            $skus[] = strtolower($stockSku);
        }

        $newOrUpdatedStockSkus = array_diff_uassoc($savedEntity->getStockSkuMap(), $currentEntity->getStockSkuMap(), 'strcasecmp');
        foreach (array_keys($newOrUpdatedStockSkus) as $stockSku) {
            $skus[] = strtolower($stockSku);
        }

        $skus = array_unique($skus);
        if (!empty($skus)) {
            ($this->allocatedStockCorrectionGearmanJobGenerator)(
                $savedEntity->getOrganisationUnitId(),
                ...$skus
            );
        }
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
            /** @var StockLocations $stockLocations */
            $stockLocations = $this->stockLocationStorage->fetchCollectionByFilter(
                (new StockLocationFilter('all', 1))->setOuIdSku(array_keys($ouIdSkuUpdateMap))
            );
            /** @var StockCollection $stocks */
            $stocks = $this->stockStorage->fetchCollectionByFilter(
                (new StockFilter('all', 1))->setId($stockLocations->getArrayOf('stockId'))
            );
        } catch (NotFound $exception) {
            // No stock locations to update
            return;
        }

        /** @var StockLocation $stockLocation */
        foreach ($stockLocations as $stockLocation) {
            $stock = $stocks->getById($stockLocation->getStockId());
            if (!($stock instanceof Stock)) {
                continue;
            }

            $ouIdSku = $this->generateOuIdSkuForStock($stock);
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
        if ($stockLocations) {
            $this->invalidateStockLocations($stockLocations);
        }
    }

    protected function generateOuIdSkuForProductLink(ProductLink $productLink)
    {
        return ProductLink::generateId($productLink->getOrganisationUnitId(), $productLink->getProductSku());
    }

    protected function generateOuIdSkuForStock(Stock $stock)
    {
        return ProductLink::generateId($stock->getOrganisationUnitId(), $stock->getSku());
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
        $this->productStockNginxCacheInvalidator->invalidateProductsForStockLocations(
            $stockLocations,
            $stockCollection
        );
    }
}