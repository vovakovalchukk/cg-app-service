<?php
namespace CG\Product\LinkNode\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Product\LinkNode\Collection;
use CG\Product\LinkNode\Filter;
use CG\Product\LinkNode\Mapper;
use CG\Product\LinkNode\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\SaveInterface;

class Cache extends CacheAbstract implements StorageInterface, SaveInterface, SaveCollectionInterface, LoggerAwareInterface
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

    public function invalidate($id)
    {
        try {
            $this->remove(
                $this->fetch($id)
            );
        } catch (NotFound $exception) {
            // Not in cache so can't invalidate
        }
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray())
        );
    }
}