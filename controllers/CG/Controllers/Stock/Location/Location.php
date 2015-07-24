<?php
namespace CG\Controllers\Stock\Location;

use CG\CGLib\Gearman\Generator\UpdateRelatedListingsForStock;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service;
use CG\Stock\Service as StockService;
use CG\Slim\ControllerTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Validation\PaginationInterface;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;
use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;

class Location implements PaginationInterface
{
    use ControllerTrait, GetTrait, PutTrait, DeleteTrait;

    protected $updateRelatedListingsForStock;
    protected $stockService;
    protected $stockLocationMapper;

    public function __construct(
        UpdateRelatedListingsForStock $updateRelatedListingsForStock,
        StockLocationMapper $stockLocationMapper,
        Slim $app,
        Service $service,
        Di $di,
        StockService $stockService,
        Invalidator $invalidator
    ) {
        $this->setUpdateRelatedListingsForStock($updateRelatedListingsForStock)
            ->setStockLocationMapper($stockLocationMapper)
            ->setSlim($app)
            ->setService($service)
            ->setStockService($stockService)
            ->setDi($di)
            ->setInvalidator($invalidator);
    }

    public function put($id, Hal $hal)
    {
        $stockLocationHal = $this->getService()->saveHal($hal, ["id" => $id]);
        try {
            $stockLocation = $this->getStockLocationMapper()->fromHal($stockLocationHal);
            $stockId = $stockLocation->getStockId();
            $stock = $this->getStockService()->fetch($stockId);
            $this->getInvalidator()->invalidateProductsForStock($stockLocation, $stock);
            $this->getUpdateRelatedListingsForStock()->generateJob($stock);
        } catch (\Exception $e) {
            // No-op. Save succeeded, everything else is superfluous
        }
        return $stockLocationHal;
    }

    protected function setStockService($stockService)
    {
        $this->stockService = $stockService;
        return $this;
    }

    protected function getStockService()
    {
        return $this->stockService;
    }

    protected function setUpdateRelatedListingsForStock($updateRelatedListingsForStock)
    {
        $this->updateRelatedListingsForStock = $updateRelatedListingsForStock;
        return $this;
    }

    protected function getUpdateRelatedListingsForStock()
    {
        return $this->updateRelatedListingsForStock;
    }

    protected function setStockLocationMapper($stockLocationMapper)
    {
        $this->stockLocationMapper = $stockLocationMapper;
        return $this;
    }

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
