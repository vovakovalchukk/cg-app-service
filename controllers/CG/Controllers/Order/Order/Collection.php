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
            $filterEntity = new FilterEntity(
                    $this->getParams('limit'),
                    $this->getParams('page'),
                    $this->getParams('id') ?: [],
                    $this->getParams('organisationUnitId') ?: [],
                    $this->getParams('status') ?: [],
                    $this->getParams('accountId') ?: [],
                    $this->getParams('channel') ?: [],
                    $this->getParams('country') ?: [],
                    $this->getParams('countryExclude') ?: [],
                    $this->getParams('shippingMethod') ?: [],
                    $this->getParams('searchTerm'),
                    $this->getParams('includeArchived'),
                    $this->getParams('multiLineSameOrder'),
                    $this->getParams('multiSameItem'),
                    $this->getParams('batch') ?: [],
                    $this->getParams('timeFrom'),
                    $this->getParams('timeTo'),
                    $this->getParams('orderBy'),
                    $this->getParams('orderDirection')
            );
            return $this->getService()->fetchCollectionByFilterAsHal($filterEntity);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(), $e);
        }
    }
}