<?php
namespace CG\Stock\Location;

class LinkedCollection extends Collection
{
    protected $missingSkus;

    public function getMissingSkus(): array
    {
        return $this->missingSkus;
    }

    /**
     * @return self
     */
    public function setMissingSkus(array $missingSkus)
    {
        $this->missingSkus = $missingSkus;
        return $this;
    }
}