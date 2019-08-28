<?php
namespace CG\Order\Service;

use Predis\Client as Predis;

class RedactLocker
{
    protected const KEY = 'OrderRedactLocker';

    /** @var Predis */
    protected $redis;

    public function __construct(Predis $redis)
    {
        $this->redis = $redis;
    }

    protected function key(string $orderId): string
    {
        return static::KEY . '::' . $orderId;
    }

    public function canRedact(string $orderId): bool
    {
        return (bool) !$this->redis->exists($this->key($orderId));
    }

    public function preventRedaction(string $orderId, \DateTime $preventUntil): void
    {
        $ttl = $preventUntil->getTimestamp() - time();
        if ($ttl <= 0) {
            return;
        }
        $this->redis->setex($this->key($orderId), $ttl, $orderId);
    }
}