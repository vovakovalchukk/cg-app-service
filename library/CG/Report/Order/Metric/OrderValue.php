<?php
namespace CG\Report\Order\Metric;

class OrderValue implements MetricInterface
{
    const KEY = 'orderValue';

    public function getSelect()
    {
        return 'ROUND(SUM(total), 2) as ' . $this->getKey();
    }

    public function getKey()
    {
        return static::KEY;
    }
}
