<?php
namespace CG\Stock\Location;

use CG\Product\Link\Service as ProductLinkService;
use CG\Stdlib\Exception\Runtime\NotFound;

class TypedMapper extends Mapper
{
    /** @var ProductLinkService $productLinkService */
    protected $productLinkService;

    public function __construct(ProductLinkService $productLinkService)
    {
        $this->productLinkService = $productLinkService;
    }

    public function fromArray(array $stockLocation)
    {
        $entity = parent::fromArray($stockLocation);
        if (!($entity instanceof TypedEntity)) {
            return $entity;
        }
        return $entity->setType($stockLocation['type'] ?? $this->getStockLocationType($entity));
    }

    protected function getStockLocationType(TypedEntity $entity)
    {
        try {
            $this->productLinkService->fetch($entity->getOrganisationUnitId() . '-' . $entity->getSku());
            return TypedEntity::TYPE_LINKED;
        } catch (NotFound $exception) {
            return TypedEntity::TYPE_REAL;
        }
    }
}