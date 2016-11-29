<?php
namespace CG\Controllers\ExchangeRate;

use CG\Currency\Service\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PatchTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Slim\Slim;
use Zend\Di\Di;

class Entity
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;
    use PatchTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}
