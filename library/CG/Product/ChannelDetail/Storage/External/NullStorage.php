<?php
namespace CG\Product\ChannelDetail\Storage\External;

use CG\Product\ChannelDetail\ExternalInterface;
use CG\Product\ChannelDetail\NullExternal;

class NullStorage implements StorageInterface
{
    public function fetch(int $productId): ExternalInterface
    {
        return new NullExternal();
    }

    public function fetchMultiple(array $productIds): array
    {
        $external = [];
        foreach ($productIds as $productId) {
            $external[$productId] = $this->fetch($productId);
        }
        return $external;
    }

    public function save(int $productId, ExternalInterface $external): void
    {
        // NoOp
    }

    public function remove(int $productId): void
    {
        // NoOp
    }
}