<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Batch\Service as BatchService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;

class Batch
{
    use ControllerTrait;

    public function __construct(Slim $app, BatchService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($batchId)
    {
        return $this->getService()->fetchAsHal($batchId);
    }

    public function put($batchId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("id" => $batchId));
    }

    public function delete($batchId)
    {
        $this->getService()->removeById($batchId);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}
