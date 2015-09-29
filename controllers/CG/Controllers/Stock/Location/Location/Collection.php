<?php
namespace CG\Controllers\Stock\Location\Location;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Controllers\Stock\Location\InvalidationTrait;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use Exception;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait;
    use GetTrait;
    use PostTrait {
        post as postTrait;
    }
    use InvalidationTrait;

    protected $stockLocationMapper;
    protected $stockService;
    protected $invalidator;

    public function __construct(
        Slim $app,
        Service $service,
        Di $di,
        StockLocationMapper $stockLocationMapper,
        StockService $stockService,
        Invalidator $invalidator
    ) {
        $this
            ->setSlim($app)
            ->setService($service)
            ->setDi($di)
            ->setStockLocationMapper($stockLocationMapper)
            ->setStockService($stockService)
            ->setInvalidator($invalidator);
    }

    public function post(Hal $hal)
    {
        $stockLocationHal = $this->postTrait($hal);
        try {
            $this->invalidateStockLocationHal($stockLocationHal);
        } catch (Exception $exception) {
            // No-op. Save succeeded, everything else is superfluous
        }
        return $stockLocationHal;
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByPaginationAndFiltersAsHal(
            $this->getParams('limit'),
            $this->getParams('page'),
            $this->getParams('stockId') ?: [],
            $this->getParams('locationId') ?: []
        );
    }

    /**
     * @return self
     */
    protected function setStockLocationMapper(StockLocationMapper $stockLocationMapper)
    {
        $this->stockLocationMapper = $stockLocationMapper;
        return $this;
    }

    /**
     * @return StockLocationMapper
     */
    protected function getStockLocationMapper()
    {
        return $this->stockLocationMapper;
    }

    /**
     * @return self
     */
    protected function setStockService(StockService $stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    /**
     * @return StockService
     */
    protected function getStockService()
    {
        return $this->stockService;
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
