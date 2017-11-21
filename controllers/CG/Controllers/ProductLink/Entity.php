<?php
namespace CG\Controllers\ProductLink;

use CG\Product\Link\Service;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
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