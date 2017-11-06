<?php
namespace CG\Stock\Location;

use CG\Product\Link\StorageInterface as ProductLinkStorage;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stock\Entity as Stock;
use CG\Stock\StorageInterface as StockStorage;

class TypedMapper extends Mapper
{
    /** @var StockStorage $stockStorage */
    protected $stockStorage;
    /** @var ProductLinkStorage $productLinkStorage */
    protected $productLinkStorage;

    public function __construct(StockStorage $stockStorage, ProductLinkStorage $productLinkStorage)
    {
        $this->stockStorage = $stockStorage;
        $this->productLinkStorage = $productLinkStorage;
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
            /** @var Stock $stock */
            $stock = $this->stockStorage->fetch($entity->getStockId());
            $this->productLinkStorage->fetch($stock->getOrganisationUnitId() . '-' . $stock->getSku());
            return TypedEntity::TYPE_LINKED;
        } catch (NotFound $exception) {
            return TypedEntity::TYPE_REAL;
        }
    }
}