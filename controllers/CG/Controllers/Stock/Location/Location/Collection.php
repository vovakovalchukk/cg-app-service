<?php
namespace CG\Controllers\Stock\Location\Location;

use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Location\Filter;
use CG\Stock\Location\Service;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?: Service::DEFAULT_LIMIT,
                $this->getParams('page') ?: Service::DEFAULT_PAGE,
                $this->getParams('stockId') ?: [],
                $this->getParams('locationId') ?: [],
                $this->getParams('ouIdSku') ?: []
            )
        );
    }
}
