<?php
namespace CG\Controllers\OrderCounts;

use CG\Order\Shared\OrderCounts\Service as OrderCountsService;
use CG\Order\Shared\OrderCounts\Filter;
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

    public function __construct(Slim $app, OrderCountsService $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->addAllowedPatchFilter('accountId')
            ->addAllowedPatchFilter('organisationUnitId');
    }

    public function get()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            $this->getFilter()
        );
    }

    protected function getCollection()
    {
        return $this->getService()->fetchCollectionByFilter(
            $this->getFilter()
        );
    }

    protected function getFilter()
    {
        if ($this->getParams('orderFilter')) {
            return $this->getService()->fetchFilterById(
                $this->getParams('orderFilter'),
                $this->getParams('limit'),
                $this->getParams('page'),
                $this->getParams('orderBy'),
                $this->getParams('orderDirection')
            );
        }

        return $this->getDi()->newInstance(Filter::class, $this->getParams());
    }

    protected function setPatchBatchSorting(array &$filterParams)
    {
        $filterParams['orderBy'] = 'purchaseDate';
        $filterParams['orderDirection'] = 'DESC';
    }
}