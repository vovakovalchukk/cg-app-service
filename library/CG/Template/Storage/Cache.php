<?php
namespace CG\Template\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Template\Collection;
use CG\Template\Filter;
use CG\Template\Mapper;
use CG\Template\StorageInterface;

class Cache extends CacheAbstract implements StorageInterface, LoggerAwareInterface
{
    use CollectionTrait;
    use SaveTrait;
    use RemoveTrait;
    use RemoveByFieldTrait;
    use LogTrait;
    use FetchTrait {
        fetch as protected traitFetch;
    }

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type): Collection
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page', 'id', 'organisationUnitId', 'type'));
        return $this->fetchCollection($collection);
    }

    public function fetchCollectionByFilter(Filter $filter): Collection
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
        return $this->fetchCollection($collection);
    }

    public function getCollectionClass()
    {
        return Collection::class;
    }

    public function fetch($id)
    {
        try {
            return $this->traitFetch($id);
        } catch (\InvalidArgumentException $e) {
            throw new NotFound(
                'Unable to fetch template using string ID',
                0,
                $e
            );
        }
    }
}
