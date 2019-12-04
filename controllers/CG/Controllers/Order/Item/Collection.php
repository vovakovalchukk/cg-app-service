<?php
namespace CG\Controllers\Order\Item;

use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Order\Service\Item\InvalidationService as ItemService;
use CG\Order\Shared\Item\Filter;
use CG\Slim\Controller\Collection\PatchTrait;
use CG\Slim\ControllerTrait;
use CG\Stdlib\Exception\Runtime\NotFound;
use Slim\Slim;
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
            ->addAllowedPatchFilter('accountId')
            ->addAllowedPatchFilter('organisationUnitId');
    }

    protected function getFilter()
    {
        return new Filter(
            $this->getParams('limit') ?: 10,
            $this->getParams('page') ?: 1,
            $this->getParams('id') ?: [],
            $this->getParams('orderIds') ?: [],
            $this->getParams('accountId') ?: [],
            $this->getParams('externalId') ?: [],
            $this->getParams('itemSku') ?: [],
            $this->getParams('status') ?: [],
            $this->getParams('organisationUnitId') ?: [],
            $this->getParams('cgCreationDateFrom'),
            $this->getParams('cgCreationDateTo'),
            $this->getParams('purchaseDateFrom'),
            $this->getParams('purchaseDateTo'),
            $this->getParams('lastUpdateFromChannelFrom'),
            $this->getParams('lastUpdateFromChannelTo'),
            $this->getParams('externalListingId') ?: [],
            $this->getParams('hasImages'),
            $this->getParams('orderArchived'),
            $this->getParams('dispatchable'),
            $this->getParams('supplierId') ?? []
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
