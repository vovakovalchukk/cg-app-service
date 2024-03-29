<?php
namespace CG\Controllers\Order\Tag;

use CG\Order\Service\Tag\Service as TagService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, TagService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        return $this->getService()->fetchPaginatedAllForOuAsHal(
            $this->getParams('page'),
            $this->getParams('limit'),
            $this->getParams('organisationUnitId') ? $this->getParams('organisationUnitId') : array(),
            $this->getParams('tag') ? $this->getParams('tag') : array()
        );
    }
} 