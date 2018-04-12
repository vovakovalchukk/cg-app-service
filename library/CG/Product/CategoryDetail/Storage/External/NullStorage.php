<?php
namespace CG\Product\CategoryDetail\Storage\External;

use CG\Product\CategoryDetail\ExternalInterface;
use CG\Product\CategoryDetail\NullExternal;

class NullStorage implements StorageInterface
{
    public function fetch(int $productId, int $categoryId): ExternalInterface
    {
        return new NullExternal();
    }

    public function fetchMultiple(array $ids): array
    {
        $external = [];
        foreach ($ids as $id) {
            [$productId, $categoryId] = $id;
            if (!isset($external[$productId])) {
                $external[$productId] = [];
            }
            $external[$productId][$categoryId] = $this->fetch($productId, $categoryId);
        }
        return $external;
    }

    public function save(int $productId, int $categoryId, ExternalInterface $external): void
    {
        // NoOp
    }

    public function remove(int $productId, int $categoryId): void
    {
        // NoOp
    }
}