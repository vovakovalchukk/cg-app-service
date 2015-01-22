<?php
namespace CG\Controllers\Listing;

use CG\Listing\Filter;
use CG\Listing\Service\Service;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PatchTrait;
use CG\Slim\Controller\Collection\PostTrait;
use Slim\Slim;
use Zend\Di\Di;

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
        return $this->getService()->fetchCollectionByFilterAsHal(
            $this->getDi()->newInstance(Filter::class, $this->getParams())
        );
    }

    /**
     * To satisfy PatchTrait
     */
    protected function getCollection()
    {
        return $this->getData();
    }
}
 