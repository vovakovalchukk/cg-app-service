<?php
namespace CG\Controllers\Order\Item;

use CG\Order\Service\Item\Service as ItemService;
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
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get()
    {
        if($this->getParams('orderIds')) {
            try {
                return $this->getService()->fetchCollectionByOrderIdsAsHal(
                    $this->getParams('orderIds') ?: []
                );
            } catch (NotFound $e) {
                throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
            }
        }
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