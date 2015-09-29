<?php
namespace CG\Controllers\Stock;

use CG\CGLib\Nginx\Cache\Invalidator\ProductStock as Invalidator;
use CG\Stock\Entity as Stock;
use CG\Stock\Mapper as StockMapper;
use Nocarrier\Hal;

trait InvalidationTrait
{
    protected function invalidateStockHal(Hal $stockHal)
    {
        return $this->invalidateStock(
            $this->getStockMapper()->fromHal($stockHal)
        );
    }

    protected function invalidateStock(Stock $stock)
    {
        $this->getInvalidator()->invalidateProductsForStock($stock);
    }

    /**
     * @return StockMapper
     */
    abstract protected function getStockMapper();

    /**
     * @return Invalidator
     */
    abstract protected function getInvalidator();
} 
