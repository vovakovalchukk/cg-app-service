<?php
namespace CG\Controllers\Listing\StatusHistory;

use CG\Listing\StatusHistory\Service\Service;
use CG\Listing\StatusHistory\Filter;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;

/**
 * @property Slim $slim
 * @property Service $service
 */
class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait;

    public function __construct(Slim $slim, Service $service)
    {
        $this->setSlim($slim)->setService($service);
    }

    public function getData()
    {
        return $this->service->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?: null,
                $this->getParams('page') ?: null,
                $this->getParams('id') ?: [],
                $this->getParams('listingId') ?: [],
                $this->getParams('status') ?: [],
                $this->getParams('latest') ?: false
            )
        );
    }
} 
