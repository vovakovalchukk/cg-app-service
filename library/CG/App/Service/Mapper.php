<?php

namespace CG\App\Service;

use CG\App\Service\Entity as ServiceEntity;
use CG\Stdlib\Mapper\FromArrayInterface;
use Nocarrier\Hal as NocarrierHal;
use CG\Slim\Mapper\CollectionToHalTrait;
use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;

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
        $service = $hal->getData();
        return $this->fromArray($service);
    }

    public function fromArray(array $service)
    {
        return $this->getDi()->get(ServiceEntity::class, $service);
    }

    public function toHal($entity)
    {
        $hal =  $this->getDi()->get(
            Hal::class, array(
                'uri' => '/service/' . $entity->getId(),
                'data' => $entity->toArray()
            )
        );
        $hal->addLink("up", '/service');
        return $hal;
    }

    public function getEmbeddedResource(ServiceEntity $service)
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
}