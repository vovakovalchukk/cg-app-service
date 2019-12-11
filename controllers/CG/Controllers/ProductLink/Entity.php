<?php
namespace CG\Controllers\ProductLink;

use CG\Product\Link\Service;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Nocarrier\Hal;
use Slim\Slim;

class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait {
        put as putTrait;
    }
    use DeleteTrait;

    /** @var Slim $slim */
    protected $slim;
    /** @var Service $service */
    protected $service;

    public function __construct(Slim $slim, Service $service)
    {
        $this->slim = $slim;
        $this->service = $service;
    }

    public function put($id, Hal $hal)
    {
        try {
            $this->putTrait($id, $hal);
        } catch (\Throwable $exception) {
            
        }
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