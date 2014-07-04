<?php
namespace CG\Controllers\Shipping\Method;

use CG\Order\Service\Shipping\Method\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use Slim\Slim;
use Zend\Di\Di;

class Method
{
    use ControllerTrait, GetTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}