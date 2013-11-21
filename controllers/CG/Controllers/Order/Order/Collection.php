<?php
namespace CG\Controllers\Order\Order;

use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Filter\Entity as FilterEntity;
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
        try {
            $filterEntity = $this->getDi()->get(FilterEntity::class,
                array("limit" => $this->getParams('limit'),
                    "page" => $this->getParams('page'),
                    "id" => $this->getParams('id') ? $this->getParams('id') : array(),
                    "organisationUnitId" => $this->getParams('organisationUnitId') ? $this->getParams('organisationUnitId') : array(),
                    "status" => $this->getParams('status') ? $this->getParams('status') : array(),
                    "accountId" => $this->getParams('accountId') ? $this->getParams('accountId') : array(),
                    "channel" => $this->getParams('channel') ? $this->getParams('channel') : array(),
                    "country" => $this->getParams('country') ? $this->getParams('country') : array(),
                    "countryExclude" => $this->getParams('countryExclude') ? $this->getParams('countryExclude') : array(),
                    "shippingMethod" => $this->getParams('shippingMethod') ? $this->getParams('shippingMethod') : array(),
                    "searchTerm" => $this->getParams('searchTerm'),
                    "includeArchived" => $this->getParams('includeArchived'),
                    "multiLineSameOrder" => $this->getParams('multiLineSameOrder'),
                    "multiSameItem" => $this->getParams('multiSameItem'),
                    "timeFrom" => $this->getParams('timeFrom'),
                    "timeTo" => $this->getParams('timeTo'),
                    "orderBy" => $this->getParams('orderBy'),
                    "orderDirection" => $this->getParams('orderDirection')
                )
            );
            return $this->getService()->fetchCollectionAsHal($filterEntity);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}