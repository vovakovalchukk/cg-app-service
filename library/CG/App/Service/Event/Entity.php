<?php
namespace Application\Service\Event;

use CG\Stdlib\CachableInterface;
use CG\Stdlib\CachableEntityTrait;

class Entity implements CachableInterface
{
    use CachableEntityTrait;

    protected $id;
    protected $serviceId;
    protected $type;
    protected $instances;
    protected $endpoint;

    public function __construct($serviceId, $type, $instances, $endpoint)
    {
        $this->setDirty(false);
        $this->setNewlyInserted(false);
        $this->setServiceId($serviceId);
        $this->setType($type);
        $this->setInstances($instances);
        $this->setEndpoint($endpoint);
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

    public function setServiceId($serviceId)
    {
        $this->serviceId = $serviceId;
        return $this;
    }

    public function getServiceId()
    {
        return $this->serviceId;
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

    public function setInstances($instances)
    {
        $this->instances = $instances;
        return $this;
    }

    public function getInstances()
    {
        return $this->instances;
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
}
