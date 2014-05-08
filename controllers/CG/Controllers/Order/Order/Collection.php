<?php
namespace CG\Controllers\Order\Order;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Order\Service\Filter\Service as FilterService;

class Collection
{
    use ControllerTrait;

    protected $filterService;

    public function __construct(Slim $app, OrderService $service, FilterService $filterService, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setFilterService($filterService)
             ->setDi($di);
    }

    public function get()
    {
        if (! $this->getParams('orderFilter')) {
            $filterEntity = $this->getDi()->newInstance(Filter::class);
        } else {
            try {
                $filterEntity = $this->getFilterService()->fetch($this->getParams('orderFilter'));
            } catch (\CG\Stdlib\Exception\Runtime\NotFound $e) {
                throw new \Exception('orderFilter couldnt be found :(');

                // todo attach previous exception
            }
        }
//
//        print_r($this->getParams('orderFilter'));
//        print_r($filterEntity);
//        exit;

        return $this->getService()->fetchCollectionByFilterAsHal($filterEntity);
    }

    public function setFilterService(FilterService $filterService)
    {
        $this->filterService = $filterService;
        return $this;
    }

    public function getFilterService()
    {
        return $this->filterService;
    }
}