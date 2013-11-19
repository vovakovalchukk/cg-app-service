<?php
namespace CG\Controllers;

use CG\App\Service\Service as ServiceService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Service
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
            return $this->getService()->fetchAsHal($id);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}
