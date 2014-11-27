<?php
namespace CG\Controllers\Listing;

use CG\Listing\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Slim\Slim;
use Zend\Di\Di;
use CG\Listing\Nginx\Cache\Invalidator;

class Entity
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    public function __construct(Slim $app, Service $service, Di $di, Invalidator $invalidator)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->setInvalidator($invalidator);
    }

    /**
     * @return self
     */
    public function setInvalidator(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
        return $this;
    }

    /**
     * @return Invalidator
     */
    protected function getInvalidator()
    {
        return $this->invalidator;
    }
}
