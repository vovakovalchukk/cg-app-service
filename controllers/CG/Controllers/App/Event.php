<?php
namespace CG\Controllers\App;

use CG\Http\StatusCode;
use CG\App\Service\Event\Service;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Event
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($serviceId, $eventId)
    {
        try {
            return $this->getService()->fetchAsHal($eventId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($serviceId, $eventId, Hal $hal)
    {
        try {
            return $this->getService()->saveHal($hal, array("id" => $eventId, "serviceId" => $serviceId));
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function delete($serviceId, $eventId)
    {
        try {
            $this->getService()->removeById($eventId);
            $this->getSlim()->response()->setStatus(StatusCode::NO_CONTENT);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
