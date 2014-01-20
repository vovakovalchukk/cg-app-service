<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Tracking
{
    use ControllerTrait;

    public function __construct(Slim $app, TrackingService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($orderId, $trackingId)
    {
        try {
            return $this->getService()->fetchAsHal($trackingId, $orderId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderId, $trackingId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("orderId" => $orderId, "id" => $trackingId));
    }

    public function delete($orderId, $trackingId)
    {
        try {
            $this->getService()->removeById($trackingId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
