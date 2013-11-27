<?php
namespace CG\Controllers\Order\Tracking;

use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Http\StatusCode;
use Nocarrier\Hal;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, TrackingService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($orderId)
    {
        try {
            return $this->getService()->fetchCollectionByOrderIdAsHal(
                $this->getParams('limit'),
                $this->getParams('page'),
                $orderId
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function post($orderId, Hal $hal)
    {
        $hal = $this->getService()->saveHal($orderId, $hal);
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $hal;
    }
}