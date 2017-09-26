<?php
namespace CG\Controllers\Ekm\Registration;

use CG\Ekm\Registration\Service;
use CG\Ekm\Registration\Filter;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)
            ->setService($service);
    }

    public function getData()
    {
        $filter = new Filter('all', 1);
        $filter
            ->setEkmUsername($this->getParams('ekmUsername') ?: [])
            ->setToken($this->getParams('token') ?: [])
            ->setOrganisationUnitId($this->getParams('organisationUnitId') ?: []);
        return $this->getService()->fetchCollectionByFilter($filter);
    }
}