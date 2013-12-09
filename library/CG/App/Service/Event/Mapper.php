<?php

namespace CG\App\Service\Event;

use CG\App\Service\Event\Entity as EventEntity;
use Nocarrier\Hal as NocarrierHal;
use CG\Slim\Mapper\CollectionToHalTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;
use CG\Stdlib\Mapper\FromArrayInterface;

class Mapper implements FromArrayInterface
{
    use CollectionToHalTrait;

    protected $di;
    protected $eventMapper;

    const FIRST_PAGE = 1;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function fromHal(NocarrierHal $hal)
    {
        $event = $hal->getData();
        return $this->fromArray($event);
    }

    public function fromArray(array $event)
    {
        return $this->getDi()->get(EventEntity::class, $event);
    }

    public function toHal($entity)
    {
        $hal =  $this->getDi()->get(
            Hal::class, array(
                'uri' => '/service/' . $entity->getServiceId() . "/event/" . $entity->getId(),
                'data' => $entity->toArray()
            )
        );
        return $hal;
    }

    public function getEmbeddedResource(EventEntity $event)
    {
        return "event";
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
}