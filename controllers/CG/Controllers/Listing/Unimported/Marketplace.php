<?php
namespace CG\Controllers\Listing\Unimported;

use CG\Listing\Unimported\Marketplace\Filter;
use CG\Listing\Unimported\Marketplace\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Marketplace
{
    use ControllerTrait;
    use GetTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)->setService($service)->setDi($di);
    }

    public function getData()
    {
        $params = $this->getParams();
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(isset($params['organisationUnitId']) ? $params['organisationUnitId'] : [])
        );
    }
} 
