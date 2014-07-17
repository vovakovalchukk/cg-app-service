<?php
namespace CG\Controllers\Shipping\Method\Method;

use CG\Order\Service\Shipping\Method\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait;

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
            $this->getParams('channel') ?: [],
            $this->getParams('method') ?: [],
            $this->getParams('organisationUnitId') ?: []
        );
    }
}
 