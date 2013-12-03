<?php
namespace CG\Controllers\Order\Item;

use CG\Http\StatusCode;
use CG\Order\Service\Item\Fee\Service as FeeService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Fee
{
    use ControllerTrait;

    public function __construct(Slim $app, FeeService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($orderItemId, $feeId)
    {
        try {
            return $this->getService()->fetchAsHal($feeId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderItemId, $feeId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("orderItemId" => $orderItemId, "id" => $feeId));
    }

    public function delete($orderItemId, $feeId)
    {
        try {
            $this->getService()->removeById($feeId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
