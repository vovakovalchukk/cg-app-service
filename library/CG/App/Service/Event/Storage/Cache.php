<?php
namespace CG\App\Service\Event\Storage;

use CG\Cache\StorageTrait;
use CG\Cache\Storage\CollectionTrait as StorageCollectionTrait;
use CG\Cache\Strategy\EntityInterface;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategyInterface;
use CG\App\Service\Event\Storage;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\App\Service\Event\Collection;

class Cache implements Storage, SaveCollectionInterface
{
    use StorageTrait;
    use StorageCollectionTrait;
    
    const TTL = 3600;
    
    protected $entityStrategy = null;
    protected $collectionStrategy = null;
    
    public function __construct(EntityInterface $entityStrategy, CollectionStrategyInterface $collectionStrategy)
    {
        $this->setEntityStrategy($entityStrategy)
             ->setCollectionStrategy($collectionStrategy);
    }
    
    public function fetchCollectionByServiceIdAndType($serviceId, $type)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('serviceId', 'type'));
        return $this->getCollectionStrategy()->get($collection);
    }
    public function fetchCollectionByServiceId($serviceId)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('serviceId'));
        return $this->getCollectionStrategy()->get($collection);
    }
    
    public function fetchCollectionByType($type)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('type'));
        return $this->getCollectionStrategy()->get($collection);
    }
    
    public function setEntityStrategy(EntityInterface $entityStrategy)
    {
        $this->entityStrategy = $entityStrategy;
        return $this;
    }
    
    public function getEntityStrategy()
    {
        return $this->entityStrategy;
    }
    
    public function setCollectionStrategy(CollectionStrategyInterface $collectionStrategy)
    {
        $this->collectionStrategy = $collectionStrategy;
        return $this;
    }
    
    public function getCollectionStrategy()
    {
        return $this->collectionStrategy;
    }
    
    protected function getEntityClass()
    {
        // PHP 5.5 would allow us to use Entity::class if we had Entity in a use statement
        return 'Application\\Service\\Event\\Entity';
    }
    
    protected function getDefaultTtl()
    {
        return static::TTL;
    }
}