<?php
namespace CG\Controllers\ProductFilter\ProductFilter;

use CG\Product\ProductFilter\Filter;
use CG\Product\ProductFilter\RestService;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

/**
 * Class Collection
 * @package CG\Controllers\ProductFilter\ProductFilter
 * @method Di getDi()
 * @method RestService getService()
 */
class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    protected function getCollection()
    {
        return $this->getData();
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            $this->getDi()->newInstance(Filter::class, $this->getParams())
        );
    }
}
