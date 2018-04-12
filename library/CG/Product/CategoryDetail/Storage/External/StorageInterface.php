<?php
namespace CG\Product\CategoryDetail\Storage\External;

use CG\Product\CategoryDetail\ExternalInterface;

interface StorageInterface
{
    public function fetch(int $productId, int $categoryId): ExternalInterface;

    /**
     * @param int[int[]] $productIds
     * @return ExternalInterface[]
     */
    public function fetchMultiple(array $ids): array;

    public function save(int $productId, int $categoryId, ExternalInterface $external): void;

    public function remove(int $productId, int $categoryId): void;
}