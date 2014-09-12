<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Service as ServiceService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\PatchTrait;
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
    use PatchTrait;
    use LogTrait;

    public function __construct(Slim $app, ServiceService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($id)
    {
        return $this->getService()->fetchAsHal($id);
    }

    public function put($id, Hal $hal)
    {
        $this->logDebug('Order <' . $id . '> was PUT');
        return $this->getService()->saveHal($hal, array("id" => $id));
    }

    public function delete($id)
    {
        $this->getService()->removeById($id);
        $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
    }
}
