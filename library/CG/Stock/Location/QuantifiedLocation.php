<?php
namespace CG\Stock\Location;

/**
 * This class represents a stock location where we need to deal with multiples. The current use case is for making up
 * LinkedLocations where a sku is included with a qty.
 * i.e. If a linked location has sku "test" and a qty of 2, if we say there are 3 in stock of "test", we can only make 1
 * available `floor(3/2) = 1` to the linked location.
 */
class QuantifiedLocation extends Entity
{
    protected $componentMultiplier = 1;

    public function getAvailable($quantify = false)
    {
        return $this->getOnHand($quantify) - $this->getAllocated($quantify);
    }

    public function getOnHand($quantify = false): int
    {
        $onHand = parent::getOnHand();
        if ($quantify) {
            return floor($onHand / $this->componentMultiplier);
        }
        return $onHand;
    }

    public function setOnHand(int $onHand, $quantify = false): Entity
    {
        if ($quantify) {
            $onHand *= $this->componentMultiplier;
        }
        return parent::setOnHand($onHand);
    }

    public function getAllocated($quantify = false): int
    {
        $allocated = parent::getAllocated();
        if ($quantify) {
            return ceil($allocated / $this->componentMultiplier);
        }
        return $allocated;
    }

    public function setAllocated(int $allocated, $quantify = false): Entity
    {
        if ($quantify) {
            $allocated *= $this->componentMultiplier;
        }
        return parent::setAllocated($allocated);
    }

    /**
     * @return int
     */
    public function getComponentMultiplier()
    {
        return $this->componentMultiplier;
    }

    /**
     * @return self
     */
    public function setComponentMultiplier($componentMultiplier)
    {
        $this->componentMultiplier = (int) $componentMultiplier;
        return $this;
    }
}