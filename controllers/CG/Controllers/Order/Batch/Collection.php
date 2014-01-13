<?php
namespace CG\Controllers\Order\Batch;

use CG\Order\Service\Batch\Service as BatchService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;
use CG\Http\StatusCode;
use Nocarrier\Hal;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, BatchService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        return $this->getService()->fetchCollectionByPaginationAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('organisationUnitId') ? $this->getParams('organisationUnitId') : array(),
            $this->getParams('active')
        );
    }
}