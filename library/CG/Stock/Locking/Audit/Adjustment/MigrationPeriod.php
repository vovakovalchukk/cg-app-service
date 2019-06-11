<?php
namespace CG\Stock\Locking\Audit\Adjustment;

use CG\Locking\LockableInterface as Lockable;
use CG\Stdlib\Date;
use CG\Stock\Audit\Adjustment;

class MigrationPeriod extends Adjustment\MigrationPeriod implements Lockable
{
    protected const OWNER = 'MigrateStockAuditAdjustments';

    public function __construct(Adjustment\MigrationPeriod $period)
    {
        parent::__construct($period->getFrom(), $period->getTo());
    }

    public function getOwnerId()
    {
        return static::OWNER;
    }

    public function getLockKeys()
    {
        return [
            $this->generateKey('from', $this->getFrom()),
            $this->generateKey('to', $this->getTo()),
        ];
    }

    protected function generateKey(string $type, Date $dateTime): string
    {
        return implode('::', [static::OWNER, $type, $dateTime->getDate()]);
    }
}