<?php
namespace CG\Controllers\Order\Order;

use CG\Order\Service\Filter;
use CG\Order\Service\Service as OrderService;
use CG\Slim\Controller\Collection\PatchTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use PatchTrait;

    protected $filterService;

    public function __construct(Slim $app, OrderService $service, Di $di)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->addAllowedPatchFilter('accountId')
            ->addAllowedPatchFilter('organisationUnitId')
            ->addAllowedPatchFilter('orderFilter'); 
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
