<?php
namespace CG\Controllers\Stock\Location;

use CG\Stock\Location\Entity;
use CG\Stock\Location\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Location
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait{
        GetTrait::get as getTraitGet;
        PutTrait::put as putTraitPut;
        DeleteTrait::delete as deleteTraitDelete;
    }

    public function get($stockId, $locationId)
    {
        $id = Entity::convertStockAndLocationToId($stockId, $locationId);
        return $this->getTraitGet($id);
    }

    public function put($stockId, $locationId, Hal $hal)
    {
        $id = Entity::convertStockAndLocationToId($stockId, $locationId);
        return $this->putTraitPut($id, $hal);
    }

    public function delete($stockId, $locationId)
    {
        $id = Entity::convertStockAndLocationToId($stockId, $locationId);
        return $this->deleteTraitDelete($id);
    }

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}
 