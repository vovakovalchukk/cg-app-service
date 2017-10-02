<?php
namespace CG\Reporting\Order\Dimension;

use CG\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;

class Factory
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getDimension(string $dimension): DimensionInterface
    {
        $class = $this->getClassNameByString($dimension);
        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        return $this->di->get($class);
    }

    protected function getClassNameByString(string $dimension)
    {
        return __NAMESPACE__ . '\\' . ucfirst($dimension);
    }
}
