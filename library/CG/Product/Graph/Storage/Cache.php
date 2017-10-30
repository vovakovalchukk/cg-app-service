<?php
namespace CG\Product\Graph\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Product\Graph\Collection;
use CG\Product\Graph\Filter;
use CG\Product\Graph\Mapper;
use CG\Product\Graph\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\SaveInterface;

class Cache extends CacheAbstract implements StorageInterface, SaveInterface, SaveCollectionInterface, LoggerAwareInterface
{
    use FetchTrait;
    use SaveTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
    use CollectionTrait;
    use LogTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray())
        );
    }
}