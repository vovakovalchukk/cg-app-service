<?php
namespace CG\Stock\Command;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Locking\Creator as StockCreator;
use CG\Zend\Stdlib\Db\Sql\Sql as SqlClient;

class CreateMissingStock implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'CreateMissingStockCommand';

    /** @var SqlClient */
    protected $sqlClient;
    /** @var StockCreator */
    protected $stockCreator;

    public function __construct(SqlClient $sqlClient, StockCreator $stockCreator)
    {
        $this->setSqlClient($sqlClient)
            ->setStockCreator($stockCreator);
    }

    public function __invoke($dryRun = true)
    {
        $this->logDebug('CreateMissingStock command invoked %s', [($dryRun ? 'DRY RUN' : '')], static::LOG_CODE);
        $count = $this->createMissingStock($dryRun);
        $count += $this->createMissingStockLocations($dryRun);
        $this->logDebug('CreateMissingStock command finished %s', [($dryRun ? 'DRY RUN' : '')], static::LOG_CODE);
        return $count;
    }

    protected function createMissingStock($dryRun)
    {
        $skusMissingStock = $this->getSkusMissingStock();
        $count = count($skusMissingStock);
        $this->logDebug('Found %d products that are missing stock', [$count], static::LOG_CODE);
        if ($count == 0) {
            return 0;
        }
        foreach ($skusMissingStock as $details) {
            $this->logDebug('Creating stock for OU %d, SKU "%s"', [$details['organisationUnitId'], $details['sku']], static::LOG_CODE);
            $stock = $this->stockCreator->create($details['organisationUnitId'], $details['sku']);
            if (!$dryRun) {
                $this->stockCreator->save($stock);
            }
        }
        return $count;
    }

    protected function createMissingStockLocations($dryRun)
    {
        $skusMissingStockLocations = $this->getSkusMissingStockLocations();
        $count = count($skusMissingStockLocations);
        $this->logDebug('Found %d stocks that are missing stock locations', [$count], static::LOG_CODE);
        if ($count == 0) {
            return 0;
        }
        foreach ($skusMissingStockLocations as $details) {
            $this->logDebug('Creating stock location for OU %d, SKU "%s"', [$details['organisationUnitId'], $details['sku']], static::LOG_CODE);
            $stock = $this->stockCreator->fetchByOuAndSku($details['organisationUnitId'], $details['sku']);
            $this->stockCreator->createStockLocationsForStock($stock);
            if (!$dryRun) {
                $this->stockCreator->saveStockLocations($stock->getLocations(), $stock);
            }
        }
        return $count;
    }

    protected function getSkusMissingStock()
    {
        $query = 'SELECT product.sku, product.organisationUnitId FROM product '
            . 'LEFT JOIN stock ON (product.sku = stock.sku AND product.organisationUnitId = stock.organisationUnitId) '
            . 'LEFT JOIN product as variation ON (product.id = variation.parentProductId) '
            . 'WHERE stock.id IS NULL '
            . 'AND (product.parentProductId > 0 OR variation.id IS NULL) '
            . 'AND product.sku != \'\'';

        return $this->sqlClient->getAdapter()->query($query)->execute();
    }

    protected function getSkusMissingStockLocations()
    {
        $query = 'SELECT stock.sku, stock.organisationUnitId FROM stock '
            . 'LEFT JOIN stockLocation ON (stock.id = stockLocation.stockId) '
            . 'WHERE stockLocation.locationId IS NULL';

        return $this->sqlClient->getAdapter()->query($query)->execute();
    }

    protected function setSqlClient(SqlClient $sqlClient)
    {
        $this->sqlClient = $sqlClient;
        return $this;
    }

    protected function setStockCreator(StockCreator $stockCreator)
    {
        $this->stockCreator = $stockCreator;
        return $this;
    }
}
