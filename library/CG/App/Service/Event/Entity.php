<?php
namespace CG\App\Service\Event;

use CG\Stdlib\CachableInterface;
use CG\Stdlib\CachableEntityTrait;
use CG\ETag\ETagInterface;
use CG\ETag\EntityTrait as ETagEntityTrait;

class Entity implements CachableInterface, ETagInterface
{
    use CachableEntityTrait, ETagEntityTrait;

    protected $id;
    protected $serviceId;
    protected $type;
    protected $instances;
    protected $endpoint;

    public function __construct($serviceId, $type, $instances, $endpoint, $id = null)
    {
        $this->setServiceId($serviceId)
             ->setType($type)
             ->setInstances($instances)
             ->setEndpoint($endpoint)
             ->setId($id);
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

    public function toArray()
    {
        return array(
            "id" => $this->getId(),
            "serviceId" => $this->getServiceId(),
            "type" => $this->getType(),
            "instances" => $this->getInstances(),
            "endpoint" => $this->getEndpoint()
        );
    }

    public function getETagDataArray()
    {
        return $this->toArray();
    }
}
