<?php
namespace CG\Ekm\Registration\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Ekm\Registration\Collection;
use CG\Ekm\Registration\Filter;
use CG\Ekm\Registration\Mapper;
use CG\Ekm\Registration\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use CollectionTrait;
    use SaveTrait;
    use FetchTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
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