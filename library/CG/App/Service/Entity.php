<?php
namespace CG\App\Service;

use CG\Stdlib\CachableInterface;
use CG\Stdlib\CachableEntityTrait;
use CG\App\Service\Event\Collection as EventCollection;
use CG\App\Service\Event\Entity as EventEntity;
use CG\ETag\ETagInterface;
use CG\ETag\EntityTrait as ETagEntityTrait;

class Entity implements CachableInterface, ETagInterface
{
    use CachableEntityTrait, ETagEntityTrait;

    protected $id;
    protected $type;
    protected $endpoint;
    protected $events;

    public function __construct($type, $endpoint, $id = null)
    {
        $this->setType($type)
             ->setEndpoint($endpoint)
             ->setId($id);
        $this->events = new \SplObjectStorage();
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function addEvent(EventEntity $entity)
    {
        $this->getEvents()->attach($entity);
    }

    public function setEvents(EventCollection $events)
    {
        $this->events = $events;
        return $this;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function  toArray()
    {
        return array(
            "id" => $this->getId(),
            "endpoint" => $this->getEndpoint(),
            "type" => $this->getType()
        );
    }

    public function getETagDataArray()
    {
        return $this->toArray();
    }
}
