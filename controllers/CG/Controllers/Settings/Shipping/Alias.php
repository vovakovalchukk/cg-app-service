<?php

namespace CG\Controllers\Settings\Shipping;

use CG\Settings\Shipping\Alias\RestService;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Alias
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    public function __construct(Slim $app, Di $di, RestService $service)
    {
        $this->slim = $app;
        $this->di = $di;
        $this->service = $service;
    }
}