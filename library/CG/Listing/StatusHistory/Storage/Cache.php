<?php
namespace CG\Listing\StatusHistory\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Listing\StatusHistory\Collection;
use CG\Listing\StatusHistory\Entity;
use CG\Listing\StatusHistory\Filter;
use CG\Listing\StatusHistory\Mapper;
use CG\Listing\StatusHistory\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use FetchTrait;
    use RemoveTrait;
    use SaveTrait;
    use CollectionTrait;
    use LogTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection(Entity::class, __FUNCTION__, $filter->toArray())
        );
    }
} 
