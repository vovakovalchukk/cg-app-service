<?php
namespace CG\Stock\Location;

class LinkedLocation extends QuantifiedLocation
{
    const MIN_VALUE = 'min';
    const MAX_VALUE = 'max';

    /** @var LinkedCollection $linkedLocations */
    protected $linkedLocations;
    /** @var array $stockOverridden */
    protected $stockOverridden = [];

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
        return $this->getOnHand($quantify) - $this->getAllocated($quantify);
    }

    public function getOnHand($quantify = true)
    {
        return $this->getStock(substr(__FUNCTION__, 3), static::MIN_VALUE, $quantify);
    }

    public function setOnHand($onHand, $quantify = true)
    {
        $this->stockOverridden[substr(__FUNCTION__, 3)] = true;
        return parent::setOnHand($onHand, $quantify);
    }

    public function getAllocated($quantify = true)
    {
        return $this->getStock(substr(__FUNCTION__, 3), static::MAX_VALUE, $quantify);
    }

    public function setAllocated($allocated, $quantify = true)
    {
        $this->stockOverridden[substr(__FUNCTION__, 3)] = true;
        return parent::setAllocated($allocated, $quantify);
    }

    protected function getStock($method, $operator, $quantify)
    {
        if (isset($this->stockOverridden[$method])) {
            return parent::{'get' . $method}($quantify);
        }

        $stock = null;
        /** @var QuantifiedLocation $location */
        foreach ($this->linkedLocations as $location) {
            $locationStock = $location->{'get' . $method}($quantify);
            $stock = $stock !== null ? $operator($stock, $locationStock) : $locationStock;
        }
        return (int) $stock;
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
        return $this;
    }
}