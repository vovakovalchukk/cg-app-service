<?php
namespace CG\RestExample;

use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;

class Mapper
{
    const URL = '/rest';

    protected $di;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    /**
     * channelgrabber/slim contains useful traits that can be used to map stuff to Hal objects
     */
    public function entityToHal(Entity $entity)
    {
        return $this->getDi()->get(Hal::class,['uri' => static::URL, 'data' => $entity->toArray()]);
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
        return $this;
    }
}