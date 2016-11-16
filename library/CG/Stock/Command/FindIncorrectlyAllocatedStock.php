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
    const LOG_FINDINGS_ORDERS = 'Heres the same results but (for OU %d, SKU %s) based on order status instead of item status:';

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
SELECT s.sku, s.organisationUnitId, calculatedAllocated as expected, sl.allocated as actual, calculatedAllocated - allocated as diff, unknownOrders
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId
INNER JOIN (
	SELECT itemSku, order.rootOrganisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(item.`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'), itemQuantity, 0),
			IF(item.`status` IN ('awaiting payment', 'new'), itemQuantity, 0)
		)) as calculatedAllocated,
        SUM(IF(item.`status` = 'unknown', itemQuantity, 0)) as unknownOrders
	FROM item
	INNER JOIN `order` ON item.orderId = order.id
	INNER JOIN account.account ON item.accountId = account.id
	WHERE item.stockManaged = 1
	GROUP BY itemSku, order.rootOrganisationUnitId
) as calc ON (
    calc.itemSku LIKE REPLACE(REPLACE(REPLACE(s.sku, '\\\\', '\\\\\\\\'), '%', '\\\\%'), '_', '\\\\_')
    AND s.organisationUnitId = calc.rootOrganisationUnitId
)
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
            $this->logDebugDump($details, static::LOG_FINDINGS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku'], $result['actual'], $result['expected']], static::LOG_CODE, ['ticket' => 'CGIV-7567']);

            $detailsByOrder = $this->getExpectedAllocatedDetailsByOrderStatus($result);
            $this->logDebugDump($detailsByOrder, static::LOG_FINDINGS_ORDERS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku']], static::LOG_CODE, ['ticket' => 'CGIV-7567']);
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
AND `item`.`itemSku` LIKE ?
AND `item`.`itemQuantity` != 0
AND 
    (
        (`item`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'))
        OR
        (`item`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $params = [$result['organisationUnitId'], \CG\Stdlib\escapeLikeValue($result['sku'])];

        $secondaryResults = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($secondaryResults);
    }

    protected function getExpectedAllocatedDetailsByOrderStatus(array $result)
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
        (`order`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new','cancelling','dispatching','refunding'))
        OR
        (`order`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $params = [$result['organisationUnitId'], \CG\Stdlib\escapeLikeValue($result['sku'])];

        $results = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($results);
    }
}
