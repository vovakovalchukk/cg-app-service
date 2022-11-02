<?php

namespace CG\Settings\Shipping\Alias\Rule;

use CG\Settings\Shipping\Alias\Nginx\Cache\Invalidator;
use CG\Slim\Renderer\ResponseType\Hal;

class RestService extends Service
{
    protected const DEFAULT_LIMIT = 10;
    protected const DEFAULT_PAGE = 1;

    /** @var Invalidator */
    protected $invalidator;

    public function __construct(StorageInterface $repository, Mapper $mapper, Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
        parent::__construct($repository, $mapper);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        $collection = $this->repository->fetchCollectionByFilter($filter);
        return $this->mapper->collectionToHal(
            $collection,
            Mapper::URL,
            $filter->getLimit(),
            $filter->getPage(),
            $filter->toArray()
        );
    }

    public function save($entity): Hal
    {
        $response = parent::save($entity);
        $this->invalidator->invalidateAliasForRules($entity);
        return $response;
    }

    public function remove($entity)
    {
        parent::remove($entity);
        $this->invalidator->invalidateAliasForRules($entity);
    }
}