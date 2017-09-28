<?php
namespace CG\Report\Order\Metric;

class Count implements MetricInterface
{
    const KEY = 'count';

    public function getSelect()
    {
        return 'COUNT(id) as ' . $this->getKey();
    }

    public function getKey()
    {
        return static::KEY;
    }
}
