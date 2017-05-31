<?php
namespace CG\Product\Link\Service;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as NginxCacheInvalidator;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter;
use CG\Product\Link\Mapper;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as Stock;
use CG\Stock\Filter as StockFilter;
use CG\Stock\Location\Collection as StockLocations;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Location\TypedEntity as TypedStockLocation;
use CG\Stock\Service as StockService;

class Service extends BaseService
{
    const RECURSION_MSG = 'Circular dependency detected. The product you are trying to link (SKU: %s) is already used to calculate stock for another product that you are trying to link this product to (SKU: %s).';

    /** @var StockLocationService $stockLocationService */
    protected $stockLocationService;
    /** @var StockService $stockService */
    protected $stockService;
    /** @var NginxCacheInvalidator $nginxCacheInvalidator */
    protected $nginxCacheInvalidator;

    public function __construct(
        StorageInterface $storage,
        Mapper $mapper,
        StockLocationService $stockLocationService,
        StockService $stockService,
        NginxCacheInvalidator $nginxCacheInvalidator
    ) {
        parent::__construct($storage, $mapper);
        $this->stockLocationService = $stockLocationService;
        $this->stockService = $stockService;
        $this->nginxCacheInvalidator = $nginxCacheInvalidator;
    }

    public function remove($entity)
    {
        parent::remove($entity);
        $this->updateRelatedStockLocationsFromRemove($entity);
    }

    /**
     * @param ProductLink $entity
     */
    public function save($entity)
    {
        try {
            $currentEntity = $this->fetch($entity->getId());
        } catch (NotFound $exception) {
            $currentEntity = null;
        }

        $this->checkForRecursion($entity);
        $savedEntity = parent::save($entity);
        $this->updateRelatedStockLocationsFromSave($savedEntity, $currentEntity);
        return $savedEntity;
    }

    protected function checkForRecursion(ProductLink $entity, array $productSkuMap = [])
    {
        $productSkuMap[strtolower($entity->getProductSku())] = true;

        $ouIdProductSku = [];
        foreach (array_keys($entity->getStockSkuMap()) as $stockSku) {
            $productSku = strtolower($stockSku);
            if (isset($productSkuMap[$productSku])) {
                throw new RecursionException(
                    sprintf(static::RECURSION_MSG, $stockSku, $entity->getProductSku())
                );
            }
            $productSkuMap[$productSku] = true;
            $ouIdProductSku[] = $entity->getOrganisationUnitId() . '-' . $stockSku;
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

    protected function updateRelatedStockLocationsFromRemove(ProductLink $entity)
    {
        $this->updateRelatedStockLocations($entity, TypedStockLocation::TYPE_REAL);
    }

    protected function updateRelatedStockLocationsFromSave(ProductLink $savedEntity, ProductLink $currentEntity = null)
    {
        if ($currentEntity && $currentEntity->getProductSku() != $savedEntity->getProductSku()) {
            $this->updateRelatedStockLocations($currentEntity, TypedStockLocation::TYPE_REAL);
        }
        $this->updateRelatedStockLocations($savedEntity, TypedStockLocation::TYPE_LINKED);
    }

    protected function updateRelatedStockLocations(ProductLink $entity, $type)
    {
        try {
            $stockLocations = $this->stockLocationService->fetchCollectionByFilter(
                (new StockLocationFilter('all', 1))
                    ->setOuIdSku([$entity->getOrganisationUnitId() . '-' . $entity->getProductSku()])
            );
        } catch (NotFound $exception) {
            // No stock locations to update
            return;
        }

        foreach ($stockLocations as $stockLocation) {
            if ($stockLocation instanceof TypedStockLocation) {
                $stockLocation->setType($type);
            }
        }

        $this->stockLocationService->saveCollection($stockLocations);
        $this->invalidateStockLocations($stockLocations);
    }

    protected function invalidateStockLocations(StockLocations $stockLocations)
    {
        try {
            /** @var StockCollection $stockCollection */
            $stockCollection = $this->stockService->fetchCollectionByFilter(
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