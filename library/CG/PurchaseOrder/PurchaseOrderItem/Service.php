<?php
namespace CG\PurchaseOrder\PurchaseOrderItem;

use CG\PurchaseOrder\PurchaseOrderItem\Filter;
use CG\PurchaseOrder\PurchaseOrderItem\Mapper;
use CG\PurchaseOrder\PurchaseOrderItem\Repository;

use CG\PurchaseOrder\PurchaseOrderItem\Storage\Api;
use CG\Stdlib\ServiceTrait;

class Service
{
    use ServiceTrait;

    public function __construct(Repository $repository, Mapper $mapper)
    {
        $this->setRepository($repository)->setMapper($mapper);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->getRepository()->fetchCollectionByFilter($filter);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {

        $filterData = $filter->toArray();
        $limit = $filterData['limit'];
        $page = $filterData['page'];
        unset($filterData['limit'], $filterData['page']);

        return $this->getMapper()->collectionToHal(
            $this->getRepository()->fetchCollectionByFilter($filter),
            Api::URL,
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
     * @return Repository
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
