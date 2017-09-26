<?php
namespace CG\Controllers\Ekm\Registration;

use CG\Ekm\Registration\Filter;
use CG\Ekm\Registration\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        $filter = new Filter('all', 1);
        $filter
            ->setId($this->getParams('id') ?: [])
            ->setEkmUsername($this->getParams('ekmUsername') ?: [])
            ->setToken($this->getParams('token') ?: []);
        return $this->getService()->fetchCollectionByFilterAsHal($filter);
    }
}