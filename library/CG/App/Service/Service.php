<?php
namespace CG\App\Service;

use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;
use CG\App\Service\Repository;
use CG\App\Service\Mapper;
use CG\App\Service\Entity;
use CG\App\Service\Event\Service as EventService;

class Service
{
    protected $repository;
    protected $mapper;
    protected $eventService;

    public function __construct(Repository $repository, Mapper $mapper, EventService $eventService)
    {
        $this->setRepository($repository)
             ->setMapper($mapper)
             ->setEventService($eventService);
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        return $this->getMapper()->toHal($entity);
    }

    protected function fetch($id)
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

    public function fetchCollectionAsHal()
    {
        $collection = $this->fetchAll();
        return $this->getMapper()->collectionToHal($collection, "/service");
    }

    protected function fetchAll()
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

    public function remove($id)
    {
        $entity = $this->fetch($id);
        foreach ($entity->getSubscribedEvents() as $eventEntity) {
            $this->getEventService()->remove($eventEntity);
        }
        $this->getRepository()->remove($entity);
    }

    public function setEventService(EventService $eventService)
    {
        $this->eventService = $eventService;
        return $this;
    }

    public function getEventService()
    {
        return $this->eventService;
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
