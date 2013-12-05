<?php
namespace CG\Controllers\Order;

use CG\Http\StatusCode;
use CG\Order\Service\Service as ServiceService;
use CG\Slim\ControllerTrait;
use Selenium\Exception;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;

class Archive
{
    use ControllerTrait;

    public function __construct(Slim $app, ServiceService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($id)
    {
        try {
            return $this->getService()->fetchArchiveAsHal($id);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function put($id, Hal $hal)
    {
        return $this->getService()->archive($id, $hal);
    }
}
