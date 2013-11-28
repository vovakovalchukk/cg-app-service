<?php
namespace CG\Controllers\Order\Item\GiftWrap;

use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
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

    public function __construct(Slim $app, GiftWrapService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($orderItemId)
    {
        try {
            return $this->getService()->fetchCollectionByOrderItemIdAsHal(
                $this->getParams('limit'),
                $this->getParams('page'),
                $orderItemId
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function post($orderItemId, Hal $hal)
    {
        $hal = $this->getService()->saveHal($orderItemId, $hal);
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $hal;
    }
}