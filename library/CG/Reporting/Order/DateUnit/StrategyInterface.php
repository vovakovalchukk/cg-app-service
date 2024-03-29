<?php
namespace CG\Reporting\Order\DateUnit;

interface StrategyInterface
{
    public function getType(): string;
    public function getSelect(): string;
    public function getGroupBy(): string;
    public function getInterval(): string;
}
