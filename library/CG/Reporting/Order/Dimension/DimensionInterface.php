<?php
namespace CG\Reporting\Order\Dimension;

interface DimensionInterface
{
    public function getSelect();
    public function getKey();
    public function getGroupBy();
}
