<?php
namespace CG\Slim\Versioning;

class Version
{
    protected $min;
    protected $max;

    public function __construct($min, $max)
    {
        $this->setMin($min);
        $this->setMax($max);
    }

    public function setMin($min)
    {
        $this->min = (int) $min;
        return $this;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function setMax($max)
    {
        $this->max = (int) $max;
        return $this;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function allowedVersion($version)
    {
        $version = (int) $version;
        return $version >= $this->getMin() && $version <= $this->getMax();
    }
}