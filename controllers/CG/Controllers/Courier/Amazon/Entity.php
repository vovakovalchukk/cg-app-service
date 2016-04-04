<?php
namespace CG\Controllers\Courier\Amazon;

use CG\Amazon\Carrier\Service\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Slim\Slim;

class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)->setService($service);
    }
} 