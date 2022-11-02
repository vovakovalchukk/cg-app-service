<?php

namespace CG\Controllers\Settings\Shipping\Alias;

use CG\Settings\Shipping\Alias\Rule\Filter;
use CG\Settings\Shipping\Alias\Rule\RestService;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Rule
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit'),
                $this->getParams('page'),
                $this->getParams('id') ?: [],
                $this->getParams('shippingAliasId') ?: []
            )
        );
    }
}