<?php
namespace CG\Stock\Audit\Adjustment\Storage\FileStorage;

use CG\Stock\Audit\Adjustment\Entity as AuditAdjustment;

class File extends \ArrayObject
{
    /** @var int */
    protected $initialCount = 0;

    /**
     * @return self
     */
    public function setInitialCount(int $initialCount)
    {
        $this->initialCount = $initialCount;
        return $this;
    }

    public function isModified(): bool
    {
        return $this->initialCount !== $this->count();
    }

    public function toArray(): array
    {
        $array = [];
        /** @var AuditAdjustment $entity */
        foreach ($this as $entity) {
            $array[] = $entity->toArray();
        }
        return $array;
    }
}