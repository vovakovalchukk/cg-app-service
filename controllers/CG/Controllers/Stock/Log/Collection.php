<?php
namespace CG\Controllers\Stock\Log;

use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Audit\Combined\Filter;
use CG\Stock\Audit\Combined\Service;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use GetTrait;

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
                $this->getParams('organisationUnitId') ?: [],
                $this->getParams('sku') ?: [],
                $this->getParams('itemStatus') ?: [],
                $this->getParams('dateTimeFrom') ?: null,
                $this->getParams('dateTimeTo') ?: null,
                $this->getParams('dateTimePeriod') ?: null,
                $this->getParams('type') ?: [],
                $this->getParams('searchTerm') ?: null,
                $this->getParams('sortBy') ?: null,
                $this->getParams('sortDirection') ?: null
            )
        );
    }
}
