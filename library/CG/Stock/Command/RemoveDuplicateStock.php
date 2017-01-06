<?php
namespace CG\Stock\Command;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stock\Service as StockService;
use CG\Zend\Stdlib\Db\Sql\Sql as SqlClient;

class RemoveDuplicateStock implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'RemoveDuplicateStockCommand';

    /** @var SqlClient */
    protected $sqlClient;
    /** @var StockService */
    protected $stockService;

    public function __construct(SqlClient $sqlClient, StockService $stockService)
    {
        $this->sqlClient = $sqlClient;
        $this->stockService = $stockService;
    }

    public function __invoke($dryRun = true)
    {
        $this->logDebug('RemoveDuplicateStock command invoked %s', [($dryRun ? 'DRY RUN' : '')], [static::LOG_CODE, 'Invoked']);
        $duplicatedStock = $this->getDuplicatedStock();
        $count = count($duplicatedStock);
        $this->logDebug('Found %d stock rows that are / have duplicates', [$count], [static::LOG_CODE, 'Count']);
        if ($count == 0) {
            return 0;
        }
        $removedCount = 0;
        $first = null;
        foreach ($duplicatedStock as $details) {
            if ($first == null ||
                $first['organisationUnitId'] != $details['organisationUnitId'] ||
                strcasecmp($first['sku'], $details['sku']) !== 0
            ) {
                $this->logDebug('Processing OU %d, SKU "%s"', [$details['organisationUnitId'], $details['sku']], [static::LOG_CODE, 'Processing']);
                // Keep the first stock record for each OU/SKU
                $first = $details;
                continue;
            }
            $this->removeStockById($details['id'], $dryRun);
            $removedCount++;
        }
        $this->logDebug('RemoveDuplicateStock command finished %s- removed %d stock entries', [($dryRun ? 'DRY RUN ' : ''), $removedCount], [static::LOG_CODE, 'Done']);
        return $removedCount;
    }

    protected function getDuplicatedStock()
    {
        $query = <<<EOS
SELECT stock.id, stock.sku, stock.organisationUnitId
FROM stock
JOIN (
	SELECT sku, organisationUnitId, length(sku) as length, count(*) as count
    FROM stock
    GROUP BY sku, organisationUnitId, length
    HAVING count > 1
) AS duplicates ON (stock.sku = duplicates.sku and stock.organisationUnitId = duplicates.organisationUnitId)
EOS;

        return $this->sqlClient->getAdapter()->query($query)->execute();
    }

    protected function removeStockById($id, $dryRun)
    {
        $stock = $this->stockService->fetch($id);
        $logMsgPrefix = ($dryRun) ? 'Dry run - would have removed' : 'Removing';
        $this->logDebug($logMsgPrefix . ' Stock %d, OU %d, SKU %s', ['stock' => $stock->getId(), 'ou' => $stock->getOrganisationUnitId(), 'sku' => $stock->getSku()], [static::LOG_CODE, 'Removing']);
        if ($dryRun) {
            return;
        }
        $this->stockService->remove($stock);
    }
}
