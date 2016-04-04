<?php
namespace CG\Controllers\Courier\Amazon;

use CG\Amazon\Carrier\Filter;
use CG\Amazon\Carrier\Service\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

/**
 * @method Service getService
 */
class Collection
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    use ControllerTrait;
    use GetTrait;

    public function __construct(Slim $app, Service $service)
    {
        $this->setSlim($app)->setService($service);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?: static::DEFAULT_LIMIT,
                $this->getParams('page') ?: static::DEFAULT_PAGE,
                $this->getParams('id') ?: [],
                $this->getParams('region') ?: [],
                $this->getParams('carrier') ?: [],
                $this->getParams('service') ?: [],
                $this->getParams('currencyCode') ?: [],
                $this->getParams('deliveryExperience') ?: [],
                $this->getParams('carrierWillPickUp') ?: []
            )
        );
    }
} 