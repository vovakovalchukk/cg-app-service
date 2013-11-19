<?php
namespace CG\App\Service;

use CG\Stdlib\CachableInterface;
use CG\Stdlib\CachableEntityTrait;

class Entity implements CachableInterface
{
    use CachableEntityTrait;

    protected $id;
    protected $type;
    protected $endpoint;
    protected $subscribedEvents;

    public function __construct($type, $endpoint)
    {
        $this->setType($type)
             ->setEndpoint($endpoint);
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

    public function  toArray()
    {
        return array(
            "id" => $this->getId(),
            "endpoint" => $this->getEndpoint(),
            "type" => $this->getType()
        );
    }
}
