<?php

namespace CG\Settings\Shipping\Alias\Rule;

use CG\Slim\Renderer\ResponseType\Hal;

class RestService extends Service
{
    protected const DEFAULT_LIMIT = 10;
    protected const DEFAULT_PAGE = 1;

    public function __construct(StorageInterface $repository, Mapper $mapper)
    {
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
}