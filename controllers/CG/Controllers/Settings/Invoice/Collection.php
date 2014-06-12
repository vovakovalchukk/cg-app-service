<?php
namespace CG\Controllers\Settings\Invoice;

use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Settings\Service\Service;

class Collection
{
    use ControllerTrait;

    protected $filterService;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        return $this->getService()->fetchCollection(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('organisationUnitId')
        );
    }
}
