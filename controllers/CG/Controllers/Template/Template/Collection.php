<?php
namespace CG\Controllers\Template\Template;

use CG\Template\Filter;
use CG\Template\Service;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use CG\Http\StatusCode;
use Nocarrier\Hal;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionByFilterAsHal(
                new Filter(
                    $this->getParams('limit'),
                    $this->getParams('page'),
                    $this->getParams('id') ?? [],
                    $this->getParams('organisationUnitId') ?? [],
                    $this->getParams('type') ?? []
                )
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function post(Hal $hal)
    {
        $hal = $this->getService()->saveHal($hal, []);
        $this->getSlim()->response()->setStatus(StatusCode::CREATED);
        return $hal;
    }
}