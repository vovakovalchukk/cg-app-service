<?php
namespace CG\RestExample;

use CG\Slim\Mapper\ToHalTrait;
use Nocarrier\Hal;
use Zend\Di\Di;

class Mapper
{
    //use ToHalTrait;

    const URL = '/test';

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
        return $this->getDi()->get(\Nocarrier\Hal::class,array('uri' => static::URL, 'data' => $entity->toArray()));
    }

    protected function getDi()
    {
        return $this->di;
    }

    protected function setDi(Di $di)
    {
        $this->di = $di;
    }
}