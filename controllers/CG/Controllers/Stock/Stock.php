<?php
namespace CG\Controllers\Stock;

use CG\Stock\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Slim\Slim;
use Zend\Di\Di;

class Stock
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}
 