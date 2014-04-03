<?php
namespace CG\Controllers\Order\Order;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use CG\Http\Exception\Exception4xx\NotFound as HttpNotFound;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;

    public function __construct(Slim $app, OrderService $service, Di $di)
    {
        $this->setSlim($app)
             ->setService($service)
             ->setDi($di);
    }

    public function get()
    {
        $filterEntity = new Filter(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('id') ?: [],
            $this->getParams('organisationUnitId') ?: [],
            $this->getParams('status') ?: [],
            $this->getParams('accountId') ?: [],
            $this->getParams('channel') ?: [],
            $this->getParams('shippingAddressCountry') ?: [],
            $this->getParams('shippingAddressCountryExclude') ?: [],
            $this->getParams('shippingMethod') ?: [],
            $this->getParams('searchTerm'),
            $this->getParams('includeArchived'),
            $this->getParams('multiLineSameOrder'),
            $this->getParams('multiSameItem'),
            $this->getParams('batch') ?: [],
            $this->getParams('purchaseDateFrom'),
            $this->getParams('purchaseDateTo'),
            $this->getParams('orderBy'),
            $this->getParams('orderDirection'),
            $this->getParams('tag') ? $this->getParams('tag') : [],
            $this->getParams('paymentMethod') ? $this->getParams('paymentMethod') : [],
            $this->getParams('paymentReference') ? $this->getParams('paymentReference') : [],
            $this->getParams('totalFrom'),
            $this->getParams('totalTo')
        );
        return $this->getService()->fetchCollectionByFilterAsHal($filterEntity);
    }
}