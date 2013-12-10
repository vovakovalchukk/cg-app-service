<?php

namespace CG\App\Service;

use CG\App\Service\Entity as ServiceEntity;
use CG\Stdlib\Mapper\FromArrayInterface;
use Nocarrier\Hal as NocarrierHal;
use CG\Slim\Mapper\CollectionToHalTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;
use CG\App\Service\Event\Mapper as EventMapper;

class Mapper implements FromArrayInterface
{
    use CollectionToHalTrait;

    protected $di;
    protected $eventMapper;

    const FIRST_PAGE = 1;

    public function __construct(Di $di, EventMapper $eventMapper)
    {
        $this->setDi($di)
            ->setEventMapper($eventMapper);
    }

    public function fromHal(NocarrierHal $hal)
    {
        $service = $hal->getData();
        return $this->fromArray($service);
    }

    public function fromArray(array $service)
    {
        return $this->getDi()->get(ServiceEntity::class, $service);
    }

    public function toHal(ServiceEntity $entity)
    {
        $hal =  $this->getDi()->get(
            Hal::class, array(
                'uri' => '/service/' . $entity->getId(),
                'data' => $entity->toArray()
            )
        );
        foreach ($entity->getEvents() as $event) {
            $hal->addResource(
                $this->getEventMapper()->getEmbeddedResource(),
                $this->getEventMapper()->toHal($event)
            );
        }
        return $hal;
    }

    public function getEmbeddedResource()
    {
        return "service";
    }

    public function getFirstPage()
    {
        return static::FIRST_PAGE;
    }

    public function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }

    public function getDi()
    {
        return $this->di;
    }

    public function setEventMapper(EventMapper $eventMapper)
    {
        $this->eventMapper = $eventMapper;
        return $this;
    }

    public function getEventMapper()
    {
        return $this->eventMapper;
    }
}