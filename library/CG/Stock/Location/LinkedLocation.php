<?php
namespace CG\Stock\Location;

class LinkedLocation extends QuantifiedLocation
{
    /** @var LinkedCollection $linkedLocations */
    protected $linkedLocations;

    public function __construct(
        $id,
        $stockId,
        $locationId,
        LinkedCollection $linkedLocations
    ) {
        parent::__construct($stockId, $locationId, 0, 0, 0, $id);
        $this->setLinkedLocations($linkedLocations);
    }

    public function getAvailable($quantify = true): int
    {
        return parent::getAvailable($quantify);
    }

    public function getOnHand($quantify = true): int
    {
        return parent::getOnHand($quantify);
    }

    public function setOnHand(int $onHand, $quantify = true): Entity
    {
        return parent::setOnHand($onHand, $quantify);
    }

    public function getAllocated($quantify = true): int
    {
        return parent::getAllocated($quantify);
    }

    public function setAllocated(int $allocated, $quantify = true): Entity
    {
        return parent::setAllocated($allocated, $quantify);
    }

    public function getOnPurchaseOrder($quantify = true): int
    {
        return parent::getOnPurchaseOrder($quantify);
    }

    public function setOnPurchaseOrder(int $onPurchaseOrder, $quantify = true): Entity
    {
        return parent::setOnPurchaseOrder($onPurchaseOrder, $quantify);
    }

    public function getLinkedLocations(): LinkedCollection
    {
        return $this->linkedLocations;
    }

    /**
     * @return self
     */
    public function setLinkedLocations(LinkedCollection $linkedLocations)
    {
        $this->linkedLocations = $linkedLocations;
        $onHand = $this->getStock('OnHand');
        $allocated = $onHand - $this->getStock('Available');
        return $this->setOnHand($onHand)->setAllocated($allocated);
    }

    protected function getStock($type)
    {
        $stock = null;
        /** @var QuantifiedLocation $location */
        foreach ($this->linkedLocations as $location) {
            $locationStock = $location->{'get' . $type}(true);
            $stock = $stock !== null ? min($stock, $locationStock) : $locationStock;
        }
        return (int) $stock;
    }
}