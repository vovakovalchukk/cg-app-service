<?php
namespace CG\Controllers\Stock\Location\Location;

use CG\Stock\Location\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait {
        GetTrait::get as getTraitGet;
        PostTrait::post as postTraitPost;
    }

    protected $stockId;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($stockId)
    {
        $this->setStockId($stockId);
        return $this->getTraitGet();
    }

    public function post($stockId, Hal $hal)
    {
        $data = $hal->getData();
        if (!isset($data['stockId'])) {
            $data['stockId'] = $stockId;
            $hal->setData($data);
        }
        return $this->postTraitPost($hal);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByPaginationAndFiltersAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getStockId(),
            $this->getParams('locationId') ?: []
        );
    }

    protected function getStockId()
    {
        return $this->stockId;
    }

    protected function setStockId($stockId)
    {
        $this->stockId = $stockId;
        return $this;
    }
}
 