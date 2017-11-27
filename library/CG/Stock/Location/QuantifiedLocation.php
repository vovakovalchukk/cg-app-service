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
    protected $quantifier = 1;

    public function getAvailable($quantify = false)
    {
        return $this->getOnHand($quantify) - $this->getAllocated($quantify);
    }

    public function getOnHand($quantify = false)
    {
        $onHand = parent::getOnHand();
        if ($quantify) {
            return floor($onHand / $this->quantifier);
        }
        return $onHand;
    }

    public function setOnHand($onHand, $quantify = false)
    {
        if ($quantify) {
            $onHand *= $this->quantifier;
        }
        return parent::setOnHand($onHand);
    }

    public function getAllocated($quantify = false)
    {
        $allocated = parent::getAllocated();
        if ($quantify) {
            return ceil($allocated / $this->quantifier);
        }
        return $allocated;
    }

    public function setAllocated($allocated, $quantify = false)
    {
        if ($quantify) {
            $allocated *= $this->quantifier;
        }
        return parent::setAllocated($allocated);
    }

    /**
     * @return int
     */
    public function getQuantifier()
    {
        return $this->quantifier;
    }

    /**
     * @return self
     */
    public function setQuantifier($quantifier)
    {
        $this->quantifier = (int) $quantifier;
        return $this;
    }
}