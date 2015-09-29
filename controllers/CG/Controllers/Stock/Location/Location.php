<?php
namespace CG\Controllers\Stock\Location;

use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use CG\Validation\PaginationInterface;
use Exception;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Location implements PaginationInterface
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait {
        put as protected putTrait;
    }
    use DeleteTrait;
    use InvalidationTrait;

    protected $updateRelatedListingsForStock;
    protected $stockService;
    protected $stockLocationMapper;
    protected $invalidator;

    public function __construct(
        UpdateRelatedListingsForStock $updateRelatedListingsForStock,
        StockLocationMapper $stockLocationMapper,
        Slim $app,
        Service $service,
        Di $di,
        StockService $stockService,
        Invalidator $invalidator
    ) {
        $this
            ->setUpdateRelatedListingsForStock($updateRelatedListingsForStock)
            ->setStockLocationMapper($stockLocationMapper)
            ->setSlim($app)
            ->setService($service)
            ->setStockService($stockService)
            ->setDi($di)
            ->setInvalidator($invalidator);
    }

    public function put($id, Hal $hal)
    {
        $stockLocationHal = $this->putTrait($id, $hal);
        try {
            $stock = $this->invalidateStockLocationHal($stockLocationHal);
            $this->getUpdateRelatedListingsForStock()->generateJob($stock);
        } catch (Exception $exception) {
            // No-op. Save succeeded, everything else is superfluous
        }
        return $stockLocationHal;
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
    protected function setUpdateRelatedListingsForStock(UpdateRelatedListingsForStock $updateRelatedListingsForStock)
    {
        $this->updateRelatedListingsForStock = $updateRelatedListingsForStock;
        return $this;
    }

    /**
     * @return UpdateRelatedListingsForStock
     */
    protected function getUpdateRelatedListingsForStock()
    {
        return $this->updateRelatedListingsForStock;
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
    public function setInvalidator(Invalidator $invalidator)
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
