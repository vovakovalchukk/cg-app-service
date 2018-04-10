<?php
namespace CG\Controllers\ProductChannelDetail\ProductChannelDetail;

use CG\Product\ChannelDetail\Filter;
use CG\Product\ChannelDetail\Service;
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
        return $this->getData();
    }

    public function getData()
    {
        $params = $this->getParams();
        return new Filter(
            $params['limit'] ?? 10,
            $params['page'] ?? 1,
            $params['id'] ?? [],
            $params['productId'] ?? [],
            $params['channel'] ?? [],
            $params['organisationUnitId'] ?? []
        );
    }
}