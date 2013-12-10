<?php
namespace CG\App\Service\Storage;

use CG\App\Service\Entity;
use CG\App\Service\Collection;
use CG\Cache\StorageTrait;
use CG\Cache\Storage\CollectionTrait as StorageCollectionTrait;
use CG\Cache\Strategy\EntityInterface;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategyInterface;
use CG\App\Service\StorageInterface;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Cache\CacheAbstract;

class Cache extends CacheAbstract implements StorageInterface, SaveCollectionInterface
{
    use StorageTrait, StorageCollectionTrait;

    public function fetchCollectionWithPagination($limit, $page)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page'));
        return $this->getCollectionStrategy()->get($collection);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}