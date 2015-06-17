<?php
namespace CG\Controllers;

use CG\Root\Mapper;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Root
{
    use ControllerTrait;

    protected $mapper;

    public function __construct(Slim $app, Mapper $mapper)
    {
        $this->setSlim($app)
            ->setMapper($mapper);
    }

    public function get()
    {
        return $this->getMapper()->getHal();
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        return $this->mapper;
    }
}