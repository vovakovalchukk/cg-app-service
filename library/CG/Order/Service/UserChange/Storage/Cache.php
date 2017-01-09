<?php
namespace CG\Order\Service\UserChange\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Order\Service\Storage\CacheTrait;
use CG\Order\Shared\UserChange\Collection;
use CG\Order\Shared\UserChange\Mapper;
use CG\Order\Shared\UserChange\StorageInterface;
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

    public function fetchCollectionByOrderIds(array $orderIds)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('orderIds'));
        return $this->fetchCollection($collection);
    }
}
