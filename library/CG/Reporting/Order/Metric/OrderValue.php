<?php
namespace CG\Reporting\Order\Metric;

class OrderValue implements MetricInterface
{
    const KEY = 'orderValue';

    public function getSelect()
    {
        return 'ROUND(SUM(total / COALESCE(exchangeRate, 1)), 2) as ' . $this->getKey();
    }

    public function getKey()
    {
        return static::KEY;
    }
}
