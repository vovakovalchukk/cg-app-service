<?php
namespace CG\Controllers\OrderCounts;

use CG\CGLib\Nginx\Cache\Invalidator\OrderCounts as OrderCountsNginxCacheInvalidator;
use CG\Order\Shared\OrderCounts\Service as ServiceService;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PatchTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Slim\Slim;
use Zend\Di\Di;

class OrderCounts implements LoggerAwareInterface
{
    use ControllerTrait;
    use PatchTrait;
    use LogTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    protected $cacheInvalidator;

    public function __construct(Slim $app, ServiceService $service, Di $di, OrderCountsNginxCacheInvalidator $cacheInvalidator)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
        $this->cacheInvalidator = $cacheInvalidator;
    }

    public function purge($organisationUnitId)
    {
        $this->cacheInvalidator->clearCache($organisationUnitId);
    }
}