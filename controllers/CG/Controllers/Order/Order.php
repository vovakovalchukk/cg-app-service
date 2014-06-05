<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Service as ServiceService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Constant\Log\Service\Api;

class Order implements LoggerAwareInterface
{
    use ControllerTrait;
    use LogTrait;

    public function __construct(Slim $app, ServiceService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($id)
    {
        try {
            return $this->getService()->fetchAsHal($id);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($id, Hal $hal)
    {
        $this->log('Put method called on Order Controller', 0, 'info', __NAMESPACE__);
        return $this->getService()->saveHal($hal, array("id" => $id));
    }

    public function delete($id)
    {
        try {
            $this->getService()->removeById($id);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
