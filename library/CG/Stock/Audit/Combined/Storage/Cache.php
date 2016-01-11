<?php
namespace CG\Stock\Audit\Combined\Storage;

use CG\Stock\Audit\Combined\Collection;
use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Mapper;
use CG\Stock\Audit\Combined\StorageInterface;
use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use CollectionTrait;
    use LogTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
        return $this->fetchCollection($collection);
    }
}
