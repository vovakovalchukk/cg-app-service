<?php
namespace CG\Controllers\ProductPickingLocation;

use CG\Product\PickingLocation\Filter;
use CG\Product\PickingLocation\Service;
use CG\Slim\Controller\Collection\GetTrait;
use Slim\Slim;

class Collection
{
    use GetTrait;

    /** @var Slim */
    protected $slim;
    /** @var Service */
    protected $service;

    public function __construct(Slim $slim, Service $service)
    {
        $this->slim = $slim;
        $this->service = $service;
    }

    public function getData()
    {
        return $this->service->fetchCollectionByFilterAsHal(
            new Filter(
                $this->slim->request->params('limit', 10),
                $this->slim->request->params('page', 1),
                $this->slim->request->params('organisationUnitId', []),
                $this->slim->request->params('level', [])
            )
        );
    }
}