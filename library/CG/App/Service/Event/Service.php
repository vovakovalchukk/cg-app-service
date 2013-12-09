<?php
namespace CG\App\Service\Event;

use Nocarrier\Hal;
use CG\App\Service\Event\Repository;
use CG\App\Service\Event\Mapper;
use CG\App\Service\Event\Entity;
use CG\Stdlib\ServiceTrait;

class Service
{
    use ServiceTrait;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    public function __construct(Repository $repository, Mapper $mapper)
    {
        $this->setRepository($repository)
            ->setMapper($mapper);
    }

    public function fetchCollectionByServiceIdAsHal($limit, $page, $serviceId)
    {
        $limit = $limit ?: static::DEFAULT_LIMIT;
        $page = $page ?: static::DEFAULT_PAGE;

        $collection = $this->getRepository()->fetchCollectionByServiceId($limit, $page, $serviceId);
        return $this->getMapper()->collectionToHal(
            $collection,
            "/service/" . $serviceId . "/event",
            $limit,
            $page,
            array("serviceId" => $serviceId)
        );
    }

    public function fetchCollectionByServiceIds(array $serviceIds)
    {
        $collection = $this->getRepository()->fetchCollectionByServiceIds($serviceIds);
        return $collection;
    }

    public function remove(Entity $entity)
    {
        $this->getRepository()->remove($entity);
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}

