<?php
namespace CG\Controllers\Order\Item;

use CG\Http\StatusCode;
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
use CG\Slim\ControllerTrait;
use Selenium\Exception;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class GiftWrap
{
    use ControllerTrait;

    public function __construct(Slim $app, GiftWrapService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($orderItemId, $giftWrapId)
    {
        try {
            return $this->getService()->fetchAsHal($giftWrapId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderItemId, $giftWrapId, Hal $hal)
    {
        return $this->getService()->saveHal($orderItemId, $hal, $giftWrapId);
    }

    public function delete($orderItemId, $giftWrapId)
    {
        try {
            $this->getService()->remove($giftWrapId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
