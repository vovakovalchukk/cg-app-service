<?php
namespace CG\App\Service;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\App\Service\StorageInterface;
use CG\App\Service\Mapper;
use CG\App\Service\Entity;
use CG\App\Service\Event\Service as EventService;
use CG\Stdlib\ServiceTrait;
use CG\App\Service\Event\Collection as EventCollection;

class Service
{
    use ServiceTrait;

    protected $eventService;

    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    public function __construct(StorageInterface $repository, Mapper $mapper, EventService $eventService)
    {
        $this->setRepository($repository)
             ->setMapper($mapper)
             ->setEventService($eventService);
    }

    public function fetchCollectionWithPaginationAsHal($limit, $page)
    {
        $limit = $limit ?: static::DEFAULT_LIMIT;
        $page = $page ?: static::DEFAULT_PAGE;

        $collection = $this->getRepository()->fetchCollectionWithPagination($limit, $page);
        $collection = $this->fetchCollectionEmbeds($collection, $collection->getIds());
        return $this->getMapper()->collectionToHal($collection, "/service", $limit, $page, array());
    }

    protected function fetchCollectionEmbeds(\SplObjectStorage $collection, array $serviceIds)
    {
        try {
            $events = $this->getEventService()->fetchCollectionByServiceIds($serviceIds);
            foreach ($collection as $entity) {
                $entity->setEvents($events->getByServiceId($entity->getId()));
            }
        } catch (NotFound $e) {
            //Ignore Not Found Errors
        }
        return $collection;
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $collection = new \SplObjectStorage();
        $collection->attach($entity);
        $collection = $this->fetchCollectionEmbeds($collection, array($id));
        $collection->rewind();
        return $this->getMapper()->toHal($collection->current());
    }

    public function remove(Entity $entity)
    {
        foreach ($entity->getEvents() as $eventEntity) {
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

    public function setRepository(StorageInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}
