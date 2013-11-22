<?php
namespace CG\App\Service\Storage;

use CG\App\Service\Entity;
use CG\Cache\StorageTrait;
use CG\Cache\Storage\CollectionTrait as StorageCollectionTrait;
use CG\Cache\Strategy\EntityInterface;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategyInterface;
use CG\App\Service\Storage;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;

class Cache implements Storage, SaveCollectionInterface
{
    use StorageTrait;
    use StorageCollectionTrait;
    
    const TTL = 3600;
    
    protected $entityStrategy = null;
    protected $collectionStrategy = null;
    
    public function __construct(EntityInterface $entityStrategy, CollectionStrategyInterface $collectionStrategy)
    {
        $this->setEntityStrategy($entityStrategy);
        $this->setCollectionStrategy($collectionStrategy);
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
        return Entity::class;
    }
    
    protected function getDefaultTtl()
    {
        return static::TTL;
    }
}