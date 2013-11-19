<?php
namespace CG\App\Service\Event;

use Nocarrier\Hal;
use CG\App\Service\Event\Repository;
use CG\App\Service\Event\Mapper;
use CG\App\Service\Event\Entity;

class Service
{
    protected $repository;
    protected $mapper;

    public function __construct(Repository $repository, Mapper $mapper)
    {
        $this->setRepository($repository)
            ->setMapper($mapper);
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        return $this->getMapper()->toHal($entity);
    }

    protected function fetch($id)
    {
        return $this->getRepository()->fetch($id);
    }

    public function fetchCollectionByServiceIdAsHal($serviceId)
    {
        $collection = $this->fetchCollectionByServiceId($serviceId);
        return $this->getMapper()->collectionToHal($collection, $this->getUrl($serviceId));
    }

    protected function fetchCollectionByServiceId($serviceId)
    {
        return $this->getRepository()->fetchCollectionByServiceId($serviceId);
    }

    public function fetchByServiceIdAndTypeAsHal($serviceId, $type)
    {
        $collection = $this->fetchByServiceIdAndType($serviceId, $type);
        return $this->getMapper()->collectionToHal($collection, $this->getUrl($serviceId));
    }

    protected function getUrl($serviceId)
    {
        return "/service/" . $serviceId . "/event";
    }

    protected function fetchByServiceIdAndType($serviceId, $type)
    {
        $collection = $this->getRepository()->fetchCollectionByServiceIdAndType($serviceId, $type);
        $collection->rewind();
        return $collection->current();
    }

    public function saveHal(Hal $hal)
    {
        $entity = $this->getMapper()->fromHal($hal);
        $this->save($entity);
        return $entity;
    }

    protected function save(Entity $entity)
    {
        $this->getRepository()->save($entity);
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

