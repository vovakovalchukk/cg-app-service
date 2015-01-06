<?php
namespace CG\Controllers\Order\Item;

use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Shared\Item\Filter;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Collection\PatchTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use PatchTrait;

    public function __construct(Slim $app, ItemService $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->addAllowedPatchFilter('accountId');
    }

    protected function getFilter()
    {
        return new Filter(
            $this->getParams('limit') ?: 10,
            $this->getParams('page') ?: 1,
            $this->getParams('id') ?: [],
            $this->getParams('orderIds') ?: [],
            $this->getParams('accountId') ?: []
        );
    }

    public function get()
    {
        try {
            return  $this->getService()->fetchCollectionByFilterAsHal($this->getFilter());
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getCollection()
    {
        return  $this->getService()->fetchCollectionByFilter($this->getFilter());
    }
}
