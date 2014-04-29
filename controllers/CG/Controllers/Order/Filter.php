<?php
namespace CG\Controllers\Order;

use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use Nocarrier\Hal;
use CG\Order\Service\Filter\Service as FilterService;

class Filter
{
    use ControllerTrait;    

    public function __construct(Slim $app, FilterService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($filterId)
    {
        return $this->getService()->fetchAsHal($filterId);
    }
}
