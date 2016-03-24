<?php
namespace CG\Controllers\Listing\StatusHistory;

use CG\Listing\StatusHistory\Service\Service;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

/**
 * @property Slim $slim
 * @property Service $service
 */
class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    public function __construct(Slim $slim, Service $service)
    {
        $this->setSlim($slim)->setService($service);
    }
} 
