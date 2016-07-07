<?php
namespace CG\Controllers\Order\Label;

use CG\Order\Service\Label\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use Slim\Slim;
use Zend\Di\Di;
use CG\Order\Shared\Label\Filter;

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
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit'),
                $this->getParams('page'),
                $this->getParams('id') ?: [],
                $this->getParams('organisationUnitId') ?: [],
                $this->getParams('orderId') ?: [],
                $this->getParams('status') ?: [],
                $this->getParams('shippingAccountId') ?: [],
                $this->getParams('createdFrom') ?: null,
                $this->getParams('createdTo') ?: null
            )
        );
    }
}