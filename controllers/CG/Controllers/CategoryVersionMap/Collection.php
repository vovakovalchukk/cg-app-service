<?php
namespace CG\Controllers\CategoryVersionMap;

use CG\Product\Category\VersionMap\Filter;
use CG\Product\Category\VersionMap\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait;

    /** @var Slim $slim */
    protected $slim;
    /** @var Service $service */
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
                $this->getParams('limit') ?? 10,
                $this->getParams('page') ?? 1
            )
        );
    }

    protected function getSlim()
    {
        return $this->slim;
    }

    protected function getService()
    {
        return $this->service;
    }
}