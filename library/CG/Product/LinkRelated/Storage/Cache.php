<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\Entity\VirtualEntity;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Product\LinkRelated\Collection;
use CG\Product\LinkRelated\Entity;
use CG\Product\LinkRelated\Filter;
use CG\Product\LinkRelated\Mapper;
use CG\Product\LinkRelated\StorageInterface;
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
            $entity = $this->fetch($id);
        } catch (NotFound $exception) {
            $entity = new VirtualEntity($id, Entity::class);
        }
        $this->remove($entity);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray())
        );
    }
}