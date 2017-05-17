<?php
namespace CG\Controllers\PurchaseOrderItem;

use CG\PurchaseOrder\Item\RestService;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PatchTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class PurchaseOrderItem
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;
    use PatchTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

}
