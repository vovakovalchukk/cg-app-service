<?php
namespace CG\Controllers\Tracking;

use CG\Order\Service\Tracking\Service;
use CG\Order\Shared\Tracking\Filter;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

/**
 * @method Service getService()
 */
class Collection
{
    use ControllerTrait;
    use GetTrait;

    public function __construct(Di $di, Slim $app, Service $service)
    {
        $this->setDi($di)->setSlim($app)->setService($service);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?: Filter::DEFAULT_LIMIT,
                $this->getParams('page') ?: Filter::DEFAULT_PAGE,
                $this->getParams('id') ?: [],
                $this->getParams('orderId') ?: [],
                $this->getParams('organisationUnitId') ?: [],
                $this->getParams('accountId') ?: [],
                $this->getParams('userId') ?: [],
                $this->getParams('carrier') ?: [],
                $this->getParams('number') ?: [],
                $this->getParams('status') ?: [],
                $this->getParams('shippingService') ?? []
            )
        );
    }
}
