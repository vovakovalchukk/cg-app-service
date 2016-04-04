<?php
namespace CG\Amazon\Carrier\Service;

use CG\Amazon\Carrier\Filter;
use CG\Amazon\Carrier\Mapper;
use CG\Amazon\Carrier\Service as BaseService;
use CG\Amazon\Carrier\Storage\Api;
use CG\Stdlib\ServiceTrait;

class Service
{
    use ServiceTrait;

    public function __construct(BaseService $repository, Mapper $mapper)
    {
        $this->setRepository($repository)->setMapper($mapper);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        $filterData = $filter->toArray();
        $limit = $filterData['limit'];
        $page = $filterData['page'];
        unset($filterData['limit'], $filterData['page']);

        return $this->getMapper()->collectionToHal(
            $this->getRepository()->fetchCollectionByFilter($filter),
            Api::URI,
            $limit,
            $page,
            $filterData
        );
    }

    /**
     * @return self
     */
    protected function setRepository($repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * @return BaseService
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @return self
     */
    protected function setMapper($mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return Mapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
} 