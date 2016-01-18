<?php
namespace CG\Controllers\OrderCounts;

use CG\Http\StatusCode;
use CG\Order\Shared\OrderCounts\Service as ServiceService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\PatchTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;
use Nocarrier\Hal;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Constant\Log\Service\Api;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;

class OrderCounts implements LoggerAwareInterface
{
    use ControllerTrait;
    use PatchTrait;
    use LogTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    public function __construct(Slim $app, ServiceService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}