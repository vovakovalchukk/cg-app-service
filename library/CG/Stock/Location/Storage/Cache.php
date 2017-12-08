<?php
namespace CG\Stock\Location\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Stdlib\PaginatedCollection as Collection;
use CG\Stock\Location\Filter;
use CG\Stock\Location\Mapper;
use CG\Stock\Location\StorageInterface;

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
        return $this->fetchCollectionByFilter(
            new Filter('all', 1, $stockIds)
        );
    }

    public function fetchCollectionByPaginationAndFilters($limit, $page, array $stockId, array $locationId)
    {
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page, $stockId, $locationId)
        );
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray())
        );
    }

    public function save($stockLocation, array $adjustmentIds = [])
    {
        return $this->traitSave($stockLocation);
    }
}
