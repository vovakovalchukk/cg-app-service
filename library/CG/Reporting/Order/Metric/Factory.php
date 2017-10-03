<?php
namespace CG\Reporting\Order\Metric;

use CG\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;
use function CG\Stdlib\hyphenToFullyQualifiedClassname;

class Factory
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getMetric(string $metric): MetricInterface
    {
        $class = $this->getClassNameByString($metric);
        if (!class_exists($class)) {
            throw new ClassNotFoundException($class);
        }

        return $this->di->get($class);
    }

    protected function getClassNameByString(string $metric)
    {
        return __NAMESPACE__ . '\\' . hyphenToFullyQualifiedClassname($metric);
    }
}
