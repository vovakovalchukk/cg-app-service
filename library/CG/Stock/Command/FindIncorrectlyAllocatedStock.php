<?php
namespace CG\Stock\Command;

use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Sql;

class FindIncorrectlyAllocatedStock implements LoggerAwareInterface
{
    use LogTrait;

    const LOG_CODE = 'FindIncorrectlyAllocatedStockCommand';
    const LOG_FINDINGS = 'FindIncorrectlyAllocatedStock command found OU %d, SKU %s had %d allocated but we expected %d. Details of expectation:';

    /** @var Sql */
    protected $sqlClient;
    
    public function __construct(Sql $sqlClient)
    {
        $this->sqlClient = $sqlClient;
    }

    public function findOverAllocated()
    {
        return $this->findIncorrectlyAllocated('>');
    }

    public function findUnderAllocated()
    {
        return $this->findIncorrectlyAllocated('<');
    }

    public function findIncorrectlyAllocated($operator = '!=')
    {
                $query = <<<EOF
SELECT s.sku, calc.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff, unknownOrders
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, item.organisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated,
        SUM(IF(`status` = 'unknown', itemQuantity, 0)) as unknownOrders
	FROM item
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, item.organisationUnitId
) as calc ON calc.itemSku LIKE s.sku AND s.organisationUnitId = calc.organisationUnitId
WHERE allocated {$operator} calculatedAllocated
ORDER BY organisationUnitId, sku
EOF;

        $results = $this->sqlClient->getAdapter()->query($query)->execute();
        $this->logFindings($results);

        return $results;
    }

    protected function logFindings(ResultInterface $results)
    {
        if (count($results) == 0) {
            return;
        }

        foreach ($results as $result) {
            $details = $this->getExpectedAllocatedDetails($result);
            $this->logDebugDump($details, static::LOG_FINDINGS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku'], $result['actual'], $result['expected']], static::LOG_CODE);
        }
    }

    protected function getExpectedAllocatedDetails(array $result)
    {
        $query = <<<EOF
SELECT `item`.`orderId`, `item`.`id` AS itemId, `item`.`status` AS itemStatus, `order`.`status` AS orderStatus, 
    `item`.`itemQuantity`, IF(`item`.`purchaseDate` > `account`.`account`.`cgCreationDate`, 'yes', 'no') AS afterAccountCreation
FROM `item`
JOIN `order` ON (`item`.`orderId` = `order`.`id`)
JOIN `account`.`account` ON (`order`.`accountId` = `account`.`id`)
WHERE `item`.`organisationUnitId` = ?
AND `item`.`itemSku` = ?
AND `item`.`itemQuantity` != 0
AND 
    (
        (`item`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'))
        OR
        (`item`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $params = [$result['organisationUnitId'], $result['sku']];

        $results = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($results);
    }
}
