<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class UserChange
{
    use ControllerTrait;

    public function __construct(Slim $app, UserChangeService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($orderId)
    {
        try {
            return $this->getService()->fetchAsHal($orderId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderId, Hal $hal)
    {
        return $this->getService()->saveHal(array("orderId" => $orderId), $hal);
    }

    public function delete($orderId)
    {
        try {
            $this->getService()->removeById($orderId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
