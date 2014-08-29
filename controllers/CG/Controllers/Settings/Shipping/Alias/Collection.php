<?php
namespace CG\Controllers\Settings\Shipping\Alias;

use CG\Settings\Shipping\Alias\Service;
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
        return $this->getService()->fetchCollectionByPaginationAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('id') ?: [],
            $this->getParams('organisationUnitId') ?: []
        );
    }
}
 