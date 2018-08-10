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
        parent::__construct($stockId, $locationId, 0, 0, $id);
        $this->setLinkedLocations($linkedLocations);
    }

    public function getAvailable($quantify = true)
    {
        return parent::getAvailable($quantify);
    }

    public function getOnHand($quantify = true)
    {
        return parent::getOnHand($quantify);
    }

    public function setOnHand($onHand, $quantify = true)
    {
        return parent::setOnHand($onHand, $quantify);
    }

    public function getAllocated($quantify = true)
    {
        return parent::getAllocated($quantify);
    }

    public function setAllocated($allocated, $quantify = true)
    {
        return parent::setAllocated($allocated, $quantify);
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