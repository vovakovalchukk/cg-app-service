<?php
namespace CG\Controllers\Order\Order;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    protected $filterService;

    public function __construct(Slim $app, OrderService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get()
    {
        if ($this->getParams('orderFilter')) {
            return $this->getService()->fetchCollectionByFilterId($this->getParams('orderFilter'));
        }
        return $this->getService()->fetchCollectionByFilterAsHal(
            $this->getDi()->newInstance(Filter::class, $this->getParams())
        );
    }
}
