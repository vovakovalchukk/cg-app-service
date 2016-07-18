<?php
namespace CG\Controllers\Listing;

use CG\Listing\Filter;
use CG\Listing\Service\Service;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PatchTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

/**
 * @method Service getService
 * @method Di getDi
 */
class Collection
{
    use ControllerTrait, GetTrait, PostTrait;
    use PatchTrait;

    public function __construct(Slim $app, Service $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal($this->getFilter());
    }

    /**
     * To satisfy PatchTrait
     */
    protected function getCollection()
    {
        return $this->getService()->fetchCollectionByFilter($this->getFilter());
    }

    /**
     * @return Filter
     */
    protected function getFilter()
    {
        return $this->getDi()->newInstance(Filter::class, $this->getParams());
    }
}
