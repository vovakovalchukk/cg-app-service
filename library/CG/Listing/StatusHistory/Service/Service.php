<?php
namespace CG\Listing\StatusHistory\Service;

use CG\Listing\StatusHistory\Filter;
use CG\Listing\StatusHistory\Mapper;
use CG\Listing\StatusHistory\Service as BaseService;
use CG\Listing\StatusHistory\StorageInterface;
use CG\Stdlib\ServiceTrait;

class Service extends BaseService
{
    use ServiceTrait;

    public function __construct(StorageInterface $storage, Mapper $mapper)
    {
        parent::__construct($storage);
        $this->setMapper($mapper);
    }

    public function fetchCollectionByFilterAsHal(Filter $filter)
    {
        $filterData = $filter->toArray();
        $limit = $filterData['limit'];
        $page = $filterData['page'];
        unset($filterData['limit'], $filterData['page']);

        return $this->getMapper()->collectionToHal(
            $this->fetchCollectionByFilter($filter),
            Mapper::URI,
            $limit,
            $page,
            $filterData
        );
    }

    /**
     * @return StorageInterface
     */
    public function getRepository()
    {
        return $this->storage;
    }

    /**
     * @return self
     */
    protected function setMapper(Mapper $mapper)
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
