<?php
namespace CG\Controllers\Stock\Location\Location;

use CG\Stock\Location\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByPaginationAndFiltersAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('stockId') ?: [],
            $this->getParams('locationId') ?: []
        );
    }
}
 