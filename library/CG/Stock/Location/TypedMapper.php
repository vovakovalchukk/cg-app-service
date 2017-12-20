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

    public function fromArray(array $stockLocationArray)
    {
        $stockLocation = parent::fromArray($stockLocationArray);
        if (!($stockLocation instanceof TypedEntity)) {
            return $stockLocation;
        }
        return $stockLocation->setType($stockLocationArray['type'] ?? $this->getStockLocationType($stockLocation));
    }

    protected function getStockLocationType(TypedEntity $stockLocation)
    {
        try {
            /** @var Stock $stock */
            $stock = $this->stockStorage->fetch($stockLocation->getStockId());
            $this->productLinkStorage->fetch($stock->getOrganisationUnitId() . '-' . $stock->getSku());
            return TypedEntity::TYPE_LINKED;
        } catch (NotFound $exception) {
            return TypedEntity::TYPE_REAL;
        }
    }
}