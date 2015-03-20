<?php
namespace CG\Controllers\Product\VariationMap\VariationMap;

use CG\Product\VariationMap\Service;
use CG\Product\VariationMap\Filter;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        try {
            return $this->getData();
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(),$e);
        }
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            $this->getDi()->newInstance(Filter::class, $this->getParams())
        );
    }
}