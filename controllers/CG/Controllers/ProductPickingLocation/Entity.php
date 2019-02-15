<?php
namespace CG\Controllers\ProductPickingLocation;

use CG\Product\PickingLocation\Service;
use CG\Slim\Controller\Entity\GetTrait;

class Entity
{
    use GetTrait;

    /** @var Service */
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /** Required by trait */
    protected function getService(): Service
    {
        return $this->service;
    }
}