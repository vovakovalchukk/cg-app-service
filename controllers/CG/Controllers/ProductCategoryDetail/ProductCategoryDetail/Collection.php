<?php
namespace CG\Controllers\ProductCategoryDetail\ProductCategoryDetail;

use CG\Product\CategoryDetail\Filter;
use CG\Product\CategoryDetail\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PatchTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait;
    use PatchTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)->setService($service)->setDi($di);
    }

    protected function getCollection()
    {
        return $this->service->fetchCollectionByFilter($this->getFilter($this->getParams()));
    }

    public function getData()
    {
        return $this->service->fetchCollectionByFilterAsHal($this->getFilter($this->getParams()));
    }

    protected function getFilter(array $params): Filter
    {
        return new Filter(
            $params['limit'] ?? 10,
            $params['page'] ?? 1,
            $params['id'] ?? [],
            $params['productId'] ?? [],
            $params['categoryId'] ?? [],
            $params['channel'] ?? [],
            $params['organisationUnitId'] ?? []
        );
    }
}