<?php
namespace CG\Stock\Storage;

use CG\Cache\CacheAbstract;
use CG\Cache\Storage\CollectionTrait;
use CG\Cache\Storage\FetchTrait;
use CG\Cache\Storage\RemoveByFieldTrait;
use CG\Cache\Storage\RemoveTrait;
use CG\Cache\Storage\SaveTrait;
use CG\Cache\Strategy\CollectionInterface as CollectionStrategy;
use CG\Cache\Strategy\EntityInterface as EntityStrategy;
use CG\Stdlib\PaginatedCollection as Collection;
use CG\Stock\Filter;
use CG\Stock\Mapper;
use CG\Stock\StorageInterface;

class Cache extends CacheAbstract implements StorageInterface
{
    use CollectionTrait, SaveTrait, RemoveTrait, RemoveByFieldTrait, FetchTrait;

    public function __construct(Mapper $mapper, EntityStrategy $entityStrategy, CollectionStrategy $collectionStrategy)
    {
        parent::__construct($mapper, $entityStrategy, $collectionStrategy);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $collection = new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray());
        return $this->fetchCollection($collection);
    }

    public function fetchCollectionByPaginationAndFilters(
        $limit, $page, array $id, array $organisationUnitId, ?array $sku, array $locationId
    ) {
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page, $id, $organisationUnitId, $sku, $locationId)
        );
    }

    public function fetchCollectionBySKUs(array $sku, array $organisationUnitId)
    {
        return $this->fetchCollectionByFilter(
            (new Filter('all', 1))->setSku($sku)->setOrganisationUnitId($organisationUnitId)
        );
    }  
}
 
