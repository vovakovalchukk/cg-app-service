<?php
namespace CG\Stock\Location\Storage;

use CG\Stock\Location\StorageInterface;
use CG\Stock\Location\Mapper;
use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Stdlib\PaginatedCollection as Collection;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;

class Cache extends CacheAbstract implements StorageInterface
{
    use CollectionTrait, RemoveTrait, RemoveByFieldTrait, FetchTrait;
    use SaveTrait {
        save as traitSave;
    }

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByStockIds(array $stockIds)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('stockIds'));
        return $this->fetchCollection($collection);
    }

    public function fetchCollectionByPaginationAndFilters($limit, $page, array $stockId, array $locationId)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page', 'stockId', 'locationId'));
        return $this->fetchCollection($collection);
    }

    public function save($entity, array $adjustmentIds = [])
    {
        return $this->traitSave($entity);
    }
}
