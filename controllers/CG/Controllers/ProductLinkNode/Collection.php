<?php
namespace CG\Controllers\ProductLinkNode;

use CG\Product\LinkNode\Filter;
use CG\Product\LinkNode\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Collection
{
    use ControllerTrait;
    use GetTrait;

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
                $this->getParams('limit') ?: 10,
                $this->getParams('page') ?: 1,
                $this->getParams('ouIdProductSku') ?: []
            )
        );
    }

    /**
     * @return Slim
     */
    protected function getSlim()
    {
        return $this->slim;
    }

    /**
     * @return Service
     */
    protected function getService()
    {
        return $this->service;
    }
}