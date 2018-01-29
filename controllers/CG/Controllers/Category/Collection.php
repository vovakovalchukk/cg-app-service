<?php
namespace CG\Controllers\Category;

use CG\Product\Category\Filter;
use CG\Product\Category\Service;
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
                $this->getParams('page') ?? 1,
                $this->getParams('id') ?? [],
                $this->getParams('parentId') ?? [],
                $this->getParams('externalId') ?? [],
                $this->getParams('channel') ?? [],
                $this->getParams('listable') ?? null,
                $this->getParams('marketplace') ?? [],
                $this->getParams('accountId') ?? [],
                $this->getParams('enabled') ?? null,
                $this->getParams('version') ?? []
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