<?php
namespace CG\Amazon\Category\ExternalData\Storage;

use CG\Amazon\Category\ExternalData\Data;
use CG\Amazon\Category\ExternalData\Repository;
use CG\Amazon\Category\ExternalData\StorageInterface;

class Migration extends Repository implements StorageInterface
{
    public function save(int $categoryId, Data $data): void
    {
        $this->storage->save($categoryId, $data);
    }
}