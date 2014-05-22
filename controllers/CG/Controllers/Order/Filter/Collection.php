<?php
namespace CG\Controllers\Order\Filter;

use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;
use CG\Order\Service\Filter\Service as FilterService;
use CG\Http\StatusCode;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, FilterService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function post(Hal $hal)
    {
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $this->getService()->saveHal($hal);
    }
}
