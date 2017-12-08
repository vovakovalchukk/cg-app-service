<?php
namespace CG\Product\Link\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Product\Link\Collection;
use CG\Product\Link\Filter;
use CG\Product\Link\Mapper;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use FetchTrait {
        fetch as protected fetchTrait;
    }
    use SaveTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
    use CollectionTrait;
    use LogTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetch($id)
    {
        return $this->fetchTrait(strtolower($id));
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray())
        );
    }
}