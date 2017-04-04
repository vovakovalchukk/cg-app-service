<?php
namespace CG\Controllers\Settings\InvoiceMapping;

use CG\Settings\Order\Filter;
use CG\Settings\Order\RestService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?: null,
                $this->getParams('page') ?: null,
                $this->getParams('id') ?: [],
                $this->getParams('organisationUnitId') ?: [],
                $this->getParams('accountId') ?: []
            )
        );
    }
}
