<?php
namespace CG\Product\Link\Service;

use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Mapper;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Location\Filter as StockLocationFilter;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Location\TypedEntity as TypedStockLocation;

class Service extends BaseService
{
    /** @var StockLocationService $stockLocationService */
    protected $stockLocationService;

    public function __construct(StorageInterface $storage, Mapper $mapper, StockLocationService $stockLocationService)
    {
        parent::__construct($storage, $mapper);
        $this->stockLocationService = $stockLocationService;
    }

    public function remove($entity)
    {
        parent::remove($entity);
        $this->updateRelatedStockLocation($entity, TypedStockLocation::TYPE_REAL);
    }

    public function save($entity)
    {
        $savedEntity = parent::save($entity);
        $this->updateRelatedStockLocation($savedEntity, TypedStockLocation::TYPE_LINKED);
        return $savedEntity;
    }

    protected function updateRelatedStockLocation(ProductLink $entity, $type)
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
        // TODO: Trigger nginx cache invalidation for the stock locations
    }
}