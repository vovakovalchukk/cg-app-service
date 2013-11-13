<?php
namespace Application\Service;

use Application\Urls\Service as ServiceUrls;
use Application\Controller\ServiceEntityController;
use Application\Controller\ServiceEventEntityController;
use CG\Stdlib\Exception\Runtime\NotFound;
use Application\Service\Event\Service as EventService;
use SplObjectStorage;
use Nocarrier\Hal;
use UnexpectedValueException;

class Service
{
    protected $repository;
    protected $mapper;
    protected $eventService;

    public function __construct(Repository $repository, Mapper $mapper, EventService $eventService)
    {
        $this->setRepository($repository);
        $this->setMapper($mapper);
    }

    public function setRepository(Repository$repository)
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

    public function setEventService($eventService)
    {
        $this->eventService = $eventService;
    }

    public function getEventService()
    {
        return $this->eventService;
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
        $entity = $this->getRepository()->fetch($id);
        try {
            foreach ($this->getEventService()->fetchCollectionByServiceId($id) as $event) {
                $entity->addSubscribedEvent($event);
            }
        } catch (NotFound $exception) {
            // If collection not found - assume empty collection
        }
        return $entity;
    }

    public function fetchAsHalModel($id, ServiceUrls $urls)
    {
        return $this->getMapper()->toHalModel(
            $this->fetch($id),
            $urls
        );
    }

    public function fetchAll()
    {
        $collection = $this->getRepository()->fetchAll();

        $index = array();
        foreach ($collection as $entity) {
            $index[$entity->getId()] = $entity;
        }

        try {
            foreach ($this->getEventService()->fetchAll() as $event) {
                if (!isset($index[$event->getServiceId()])) {
                    continue;
                }
                $index[$event->getServiceId()]->addSubscribedEvent($event);
            }
        } catch (NotFound $exception) {
            // If collection not found - assume empty collection
        }

        return $collection;
    }

    public function fetchAllAsHalModel(ServiceUrls $urls)
    {
        try {
            $collection = $this->fetchAll();
        } catch (NotFound $exception) {
            $collection = new SplObjectStorage();
        }

        return $this->getMapper()->collectionToHalModel($collection, $urls);
    }

    public function save(Entity $entity)
    {
        $this->getRepository()->save($entity);
    }

    public function insertFromHal(Hal $entityHal)
    {
        $entity = $this->getMapper()->fromHal($entityHal);
        if ($entity->getId() !== null) {
            throw new UnexpectedValueException('New Entities should not have a id');
        }

        $this->save($entity);
        return $entity;
    }

    public function updateFromHal($id, Hal $entityHal)
    {
        $this->fetch($id);

        $entity = $this->getMapper()->fromHal($entityHal);
        if ($entity->getId() != $id) {
            throw new UnexpectedValueException('Can not change Entity id');
        }

        $this->save($entity);
        return $entity;
    }

    public function remove(Entity $entity)
    {
        foreach ($entity->getSubscribedEvents() as $eventEntity) {
            $this->getEventService()->remove($eventEntity);
        }
        $this->getRepository()->remove($entity);
    }
}
