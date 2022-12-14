<?php

namespace CG\Controllers\Settings\Shipping\Alias\Rule;

use CG\Http\StatusCode;
use CG\Settings\Shipping\Alias\Rule\Filter;
use CG\Settings\Shipping\Alias\Rule\RestService;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait;

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
                $this->getParams('orderBy') ?: 'priority',
                $this->getParams('orderDirection') ?: 'ASC',
                $this->getParams('id') ?: [],
                $this->getParams('shippingAliasId') ?: []
            )
        );
    }

    public function post($aliasId, Hal $hal)
    {
        $hal = $this->getService()->saveHal($hal, array("shippingAliasId" => $aliasId));
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $hal;
    }
}