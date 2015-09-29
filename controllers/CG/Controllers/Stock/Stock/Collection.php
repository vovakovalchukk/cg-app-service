<?php
namespace CG\Controllers\Stock\Stock;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Controllers\Stock\InvalidationTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Service;
use Exception;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait {
        post as protected postTrait;
    }
    use InvalidationTrait;

    protected $stockMapper;
    protected $invalidator;

    public function __construct(Slim $app, Service $service, Di $di, StockMapper $stockMapper, Invalidator $invalidator)
    {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->setStockMapper($stockMapper)
            ->setInvalidator($invalidator);
    }

    public function post(Hal $hal)
    {
        $stockHal = $this->postTrait($hal);
        try {
            $this->invalidateStock($stockHal);
        } catch (Exception $exception) {
            // No-op. Save succeeded, everything else is superfluous
        }
        return $stockHal;
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByPaginationAndFiltersAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('id') ?: [],
            $this->getParams('organisationUnitId') ?: [],
            $this->getParams('sku') ?: [],
            $this->getParams('locationId') ?: []
        );
    }

    /**
     * @return self
     */
    protected function setStockMapper(StockMapper $stockMapper)
    {
        $this->stockMapper = $stockMapper;
        return $this;
    }

    /**
     * @return StockMapper
     */
    protected function getStockMapper()
    {
        return $this->stockMapper;
    }

    /**
     * @return self
     */
    protected function setInvalidator(Invalidator $invalidator)
    {
        $this->invalidator = $invalidator;
        return $this;
    }

    /**
     * @return Invalidator
     */
    protected function getInvalidator()
    {
        return $this->invalidator;
    }
}
