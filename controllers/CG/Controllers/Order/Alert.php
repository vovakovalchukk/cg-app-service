<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Alert
{
    use ControllerTrait;

    public function __construct(Slim $app, AlertService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($orderId, $alertId)
    {
        try {
            return $this->getService()->fetchAsHal($alertId, $orderId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderId, $alertId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("orderId" => $orderId, "id" => $alertId));
    }

    public function delete($orderId, $alertId)
    {
        try {
            $this->getService()->removeById($alertId, $orderId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
