<?php
namespace Application\Service\Event;

use Application\Urls\Service as ServiceUrls;
use Application\Controller\ServiceEntityController;
use Application\Controller\ServiceEventEntityController;
use CG\Stdlib\Exception\Runtime\NotFound;
use SplObjectStorage;
use Nocarrier\Hal;
use UnexpectedValueException;

class Service
{
    protected $repository;
    protected $mapper;

    public function __construct(Repository $repository, Mapper $mapper)
    {
        $this->setRepository($repository);
        $this->setMapper($mapper);
    }

    public function setRepository(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function toHalModel(ServiceUrls $urls, Entity $entity)
    {
        return $this->getMapper()->toHalModel(
            $entity,
            $urls
        );
    }

    public function fetch($id)
    {
        return $this->getRepository()->fetch($id);
    }

    public function fetchAsHalModel($id, ServiceUrls $urls)
    {
        $this->getMapper()->toHalModel($this->fetch($id), $urls);
    }

    public function fetchByServiceIdAndType($serviceId, $type)
    {
        $collection = $this->getRepository()->fetchCollectionByServiceIdAndType($serviceId, $type);
        $collection->rewind();
        return $collection->current();
    }

    public function fetchByServiceIdAndTypeAsHalModel($serviceId, $type, ServiceUrls $urls)
    {
        return $this->getMapper()->toHalModel($this->fetchByServiceIdAndType($serviceId, $type), $urls);
    }

    public function fetchAll()
    {
        return $this->getRepository()->fetchAll();
    }

    public function fetchCollectionByServiceId($serviceId)
    {
        return $this->getRepository()->fetchCollectionByServiceId($serviceId);
    }

    public function fetchCollectionByServiceIdAsHalModel($serviceId, ServiceUrls $urls)
    {
        try {
            $collection = $this->fetchCollectionByServiceId($serviceId);
        } catch (NotFound $exception) {
            $collection = new SplObjectStorage();
        }

        return $this->getMapper()->collectionToHalModel($serviceId, $collection, $urls);
    }

    public function fetchCollectionByType($type)
    {
        return $this->getRepository()->fetchCollectionByType($type);
    }

    public function save(Entity $entity)
    {
        $this->getRepository()->save($entity);
    }

    public function insertFromHal($serviceId, Hal $entityHal)
    {
        $entity = $this->getMapper()->fromHal($serviceId, $entityHal);
        try {
            $this->fetchByServiceIdAndType($serviceId, $entity->getType());
            throw new UnexpectedValueException(
                'Event of this type already exists for this Service'
            );
        } catch (NotFound $exception) {
            // NotFound Exception means we are safe to add the new event type
        }
        $this->save($entity);
        return $entity;
    }

    public function updateFromHal($serviceId, Hal $entityHal)
    {
        $entity = $this->getMapper()->fromHal($serviceId, $entityHal);
        try {
            $entity->setId(
                $this->fetchByServiceIdAndType($serviceId, $entity->getType())->getId()
            );
        } catch (NotFound $exception) {
            throw new UnexpectedValueException(
                'Event of selected type does not exists for this Service',
                0,
                $exception
            );
        }
        $this->save($entity);
        return $entity;
    }

    public function remove(Entity $entity)
    {
        $this->getRepository()->remove($entity);
    }
}
