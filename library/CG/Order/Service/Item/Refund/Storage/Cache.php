<?php
namespace CG\Order\Service\Item\Refund\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Order\Shared\Item\Refund\Collection;
use CG\Order\Shared\Item\Refund\Filter;
use CG\Order\Shared\Item\Refund\Mapper;
use CG\Order\Shared\Item\Refund\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use CollectionTrait;
    use SaveTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
    use FetchTrait;
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