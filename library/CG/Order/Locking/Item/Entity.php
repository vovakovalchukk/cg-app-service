<?php
namespace CG\Order\Locking\Item;

use CG\Locking\LockableInterface;
use CG\Order\Shared\Item\Entity as Item;

class Entity extends Item implements LockableInterface
{
    const LOCK_KEY_PREFIX = 'OrderItemLock';
    const LOCK_KEY_SEPERATOR = ':';

    /**
     * @inheritDoc
     */
    public function getOwnerId()
    {
        return $this->getOrganisationUnitId();
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
