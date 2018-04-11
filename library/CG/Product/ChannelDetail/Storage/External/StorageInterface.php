<?php
namespace CG\Product\ChannelDetail\Storage\External;

use CG\Product\ChannelDetail\ExternalInterface;

interface StorageInterface
{
    public function fetch(int $productId): ExternalInterface;

    /**
     * @param int[] $productIds
     * @return ExternalInterface[]
     */
    public function fetchMultiple(array $productIds): array;

    public function save(int $productId, ExternalInterface $external): void;

    public function remove(int $productId): void;
}