<?php
namespace CG\Controllers\Stock\Location;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Stock\Entity as Stock;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Service as StockService;
use Nocarrier\Hal;

trait InvalidationTrait
{
    /**
     * @return Stock
     */
    protected function invalidateStockLocationHal(Hal $stockLocationHal)
    {
        return $this->invalidateStockLocation(
            $this->getStockLocationMapper()->fromHal($stockLocationHal)
        );
    }

    /**
     * @return Stock
     */
    protected function invalidateStockLocation(StockLocation $stockLocation)
    {
        $stockId = $stockLocation->getStockId();
        $stock = $this->getStockService()->fetch($stockId);
        $this->getInvalidator()->invalidateProductsForStockLocation($stockLocation, $stock);
        return $stock;
    }

    /**
     * @return StockLocationMapper
     */
    abstract protected function getStockLocationMapper();

    /**
     * @return StockService
     */
    abstract protected function getStockService();

    /**
     * @return Invalidator
     */
    abstract protected function getInvalidator();
} 
