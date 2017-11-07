<?php
namespace CG\Stock\Location;

class LinkedLocation extends QuantifiedLocation
{
    const MIN_VALUE = 'min';
    const MAX_VALUE = 'max';

    /** @var Collection $linkedLocations */
    protected $linkedLocations;

    public function __construct(
        $id,
        $stockId,
        $locationId,
        Collection $linkedLocations
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
        return $this->getStock(__FUNCTION__, static::MIN_VALUE, $quantify);
    }

    public function setOnHand($onHand, $quantify = true)
    {
        $this->setStock(__FUNCTION__, $onHand, $quantify);
        return $this;
    }

    public function getAllocated($quantify = true)
    {
        return $this->getStock(__FUNCTION__, static::MAX_VALUE, $quantify);
    }

    public function setAllocated($allocated, $quantify = true)
    {
        $this->setStock(__FUNCTION__, $allocated, $quantify);
        return $this;
    }

    protected function getStock($method, $operator, $quantify)
    {
        $stock = null;
        /** @var QuantifiedLocation $location */
        foreach ($this->linkedLocations as $location) {
            $locationStock = $location->{$method}($quantify);
            $stock = $stock !== null ? $operator($stock, $locationStock) : $locationStock;
        }
        return (int) $stock;
    }

    protected function setStock($method, $value, $quantify)
    {
        if ($quantify) {
            $value *= $this->quantifier;
        }

        /** @var QuantifiedLocation $location */
        foreach ($this->linkedLocations as $location) {
            $location->{$method}($value, $quantify);
        }
    }

    /**
     * @return Collection
     */
    public function getLinkedLocations(): Collection
    {
        return $this->linkedLocations;
    }

    /**
     * @return self
     */
    public function setLinkedLocations(Collection $linkedLocations)
    {
        $this->linkedLocations = $linkedLocations;
        return $this;
    }
}