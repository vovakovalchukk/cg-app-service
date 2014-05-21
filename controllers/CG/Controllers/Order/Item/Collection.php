<?php
namespace CG\Controllers\Order\Item;

use CG\Order\Service\Item\Service as ItemService;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, ItemService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        try {
            return $this->getService()->fetchCollectionByPaginationAsHal(
                $this->getParams('limit'),
                $this->getParams('page'),
                $this->getParams('id') ?: []
            );
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}