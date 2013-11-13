<?php
namespace Application\Service;

use CG\Stdlib\CachableInterface;
use CG\Stdlib\CachableEntityTrait;
use SplObjectStorage;
use Application\Service\Event\Entity as Event;

class Entity implements CachableInterface
{
    use CachableEntityTrait;

    protected $id;
    protected $type;
    protected $endpoint;
    protected $subscribedEvents;

    public function __construct($type, $endpoint)
    {
        $this->setDirty(false);
        $this->setNewlyInserted(false);
        $this->setType($type);
        $this->setEndpoint($endpoint);
        $this->subscribedEvents = new SplObjectStorage();
    }

    protected function getCacheTypeSeparator()
    {
        return Collection::CACHE_TYPE_SEPARATOR;
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

    public function addSubscribedEvent(Event $event)
    {
        $this->subscribedEvents->attach($event);
        return $this;
    }

    public function getSubscribedEvents()
    {
        return $this->subscribedEvents;
    }
}
