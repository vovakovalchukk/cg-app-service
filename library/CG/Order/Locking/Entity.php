<?php
namespace CG\Order\Locking;

use CG\Order\Shared\Entity as Order;
use CG\Locking\LockableInterface;

class Entity extends Order implements LockableInterface
{
    const LOCK_KEY_PREFIX = 'OrderLock';
    const LOCK_KEY_SEPERATOR = ':';

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->getRootOrganisationUnitId() ?: $this->getOrganisationUnitId();
    }

    /**
     * @inheritDoc
     */
    public function getLockKeys()
    {
        return implode(
            static::LOCK_KEY_SEPERATOR,
            [
                static::LOCK_KEY_PREFIX,
                $this->getId(),
            ]
        );
    }
}
