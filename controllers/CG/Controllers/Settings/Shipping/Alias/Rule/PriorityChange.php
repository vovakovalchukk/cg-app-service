<?php

namespace CG\Controllers\Settings\Shipping\Alias\Rule;

use CG\Settings\Shipping\Alias\RestService;
use CG\Slim\ControllerTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class PriorityChange
{
    use ControllerTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function put($aliasId, Hal $hal)
    {
        $alias = $this->getService()->fetch($aliasId);
        return $this->getService()->updateRulesHalAsHal($alias, $hal);
    }
}