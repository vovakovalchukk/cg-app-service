<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\StorageInterface;

class Migration implements StorageInterface
{

    public function fetch(int $categoryId): Data
    {
        // TODO: Implement fetch() method.
    }

    public function save(int $categoryId, Data $data): void
    {
        // TODO: Implement save() method.
    }

    public function remove(int $categoryId): void
    {
        // TODO: Implement remove() method.
    }
}