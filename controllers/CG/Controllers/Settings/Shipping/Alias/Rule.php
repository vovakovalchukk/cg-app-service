<?php

namespace CG\Controllers\Settings\Shipping\Alias;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Http\StatusCode;
use CG\Settings\Shipping\Alias\Rule\RestService;
use CG\Slim\Controller\Entity\PatchTrait;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Rule
{
    use ControllerTrait;
    use PatchTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($aliasId, $ruleId)
    {
        try {
            return $this->getService()->fetchAsHal($ruleId, $aliasId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function patch($aliasId, $ruleId, Hal $hal)
    {
        $this->getService()->patchEntity($ruleId, $hal);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }

    public function put($aliasId, $ruleId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("shippingAliasId" => $aliasId, "id" => $ruleId));
    }

    public function delete($aliasId, $ruleId)
    {
        try {
            $this->getService()->removeById($ruleId, $aliasId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}