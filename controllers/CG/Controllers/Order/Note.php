<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Note\Service as NoteService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Note
{
    use ControllerTrait;

    public function __construct(Slim $app, NoteService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get($orderId, $noteId)
    {
        try {
            return $this->getService()->fetchAsHal($noteId, $orderId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($orderId, $noteId, Hal $hal)
    {
        return $this->getService()->saveHal($hal, array("orderId" => $orderId, "id" => $noteId));
    }

    public function delete($orderId, $noteId)
    {
        try {
            $this->getService()->removeById($noteId, $orderId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
