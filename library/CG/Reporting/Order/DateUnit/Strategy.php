<?php
namespace CG\Reporting\Order\DateUnit;

class Strategy implements StrategyInterface
{
    /** @var string  */
    protected $select;
    /** @var string  */
    protected $groupBy;
    /** @var string  */
    protected $type;
    /** @var string */
    protected $interval;

    public function __construct(string $type, string $select, string $groupBy, string $interval)
    {
        $this->type = $type;
        $this->select = $select;
        $this->groupBy = $groupBy;
        $this->interval = $interval;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSelect(): string
    {
        return $this->select;
    }

    public function getGroupBy(): string
    {
        return $this->groupBy;
    }

    public function getInterval(): string
    {
        return $this->interval;
    }
}
