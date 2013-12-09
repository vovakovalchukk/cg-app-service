<?php
namespace CG\App\Service\Event\Storage;

use CG\Cache\StorageTrait;
use CG\Cache\Storage\CollectionTrait as StorageCollectionTrait;
use CG\Cache\Strategy\EntityInterface;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategyInterface;
use CG\App\Service\Event\StorageInterface;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\App\Service\Event\Collection;
use CG\Cache\CacheAbstract;
use CG\Cache\GetEntityClassTrait;

class Cache extends CacheAbstract implements StorageInterface, SaveCollectionInterface
{
    use StorageTrait, StorageCollectionTrait, GetEntityClassTrait;

    public function fetchCollectionByServiceId($limit, $page, $serviceId)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page', 'serviceId'));
        return $this->getCollectionStrategy()->get($collection);
    }

    public function fetchCollectionByServiceIds(array $serviceIds)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('serviceIds'));
        return $this->getCollectionStrategy()->get($collection);
    }
}