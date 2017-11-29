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
INNER JOIN location AS l ON sl.locationId = l.id AND l.type = 'Merchant'
INNER JOIN (
    SELECT IFNULL(productLink.leafSku, item.itemSku) as allocatedSku, order.rootOrganisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(item.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding'), item.itemQuantity, 0),
			IF(item.`status` IN ('awaiting payment', 'new'), item.itemQuantity, 0)
		)) * IFNULL(productLink.quantity, 1) as calculatedAllocated,
        SUM(IF(item.`status` = 'unknown', item.itemQuantity, 0)) * IFNULL(productLink.quantity, 1) as unknownOrders
	FROM item
	INNER JOIN `order` ON item.orderId = order.id
	INNER JOIN account.account ON item.accountId = account.id
    LEFT JOIN (
		SELECT root.organisationUnitId, root.sku, leaf.sku as leafSku, SUM(leafPath.quantity) as quantity
		FROM productLink root
		JOIN productLinkPath rootPath ON root.linkId = rootPath.linkId and rootPath.order = 0
		JOIN (SELECT pathId, MAX(`order`) as `order` FROM productLinkPath GROUP BY pathId) leafPathOrder ON rootPath.pathId = leafPathOrder.pathId
		JOIN productLinkPath leafPath ON leafPathOrder.pathId = leafPath.pathId and leafPathOrder.order = leafPath.order
		JOIN productLink leaf ON leafPath.linkId = leaf.linkId
		GROUP BY root.organisationUnitId, root.sku, leaf.sku
    ) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\\\%'), '_', '\\\\_')
	WHERE item.itemSku != ''
	AND item.stockManaged = 1
	AND item.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding', 'unknown')
	GROUP BY allocatedSku, order.rootOrganisationUnitId
) as calc ON (
    calc.allocatedSku LIKE REPLACE(REPLACE(REPLACE(s.sku, '\\\\', '\\\\\\\\'), '%', '\\\\%'), '_', '\\\\_')
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
        foreach ($results as $result) {
            $details = $this->getExpectedAllocatedDetails($result);
            $discrepency = $this->areExpectationAndDetailsResultsDifferent($result, $details);
            $this->logDebugDump($details, static::LOG_FINDINGS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku'], $result['actual'], $result['expected']], static::LOG_CODE, ['ticket' => 'CGIV-7567', 'discrepency' => $discrepency]);

            $detailsByOrder = $this->getExpectedAllocatedDetailsByOrderStatus($result);
            $this->logDebugDump($detailsByOrder, static::LOG_FINDINGS_ORDERS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku']], static::LOG_CODE, ['ticket' => 'CGIV-7567']);
        }
    }

    protected function getExpectedAllocatedDetails(array $result)
    {
        $query = <<<EOF
SELECT `item`.`orderId`, `item`.`id` AS itemId, `item`.`status` AS itemStatus, `order`.`status` AS orderStatus,
    `item`.`itemQuantity` * IFNULL(productLink.quantity, 1) as `itemQuantity`, IF(`item`.`purchaseDate` > `account`.`account`.`cgCreationDate`, 'yes', 'no') AS afterAccountCreation
FROM `item`
JOIN `order` ON (`item`.`orderId` = `order`.`id`)
JOIN `account`.`account` ON (`order`.`accountId` = `account`.`id`)
LEFT JOIN (
    SELECT root.organisationUnitId, root.sku, leaf.sku as leafSku, SUM(leafPath.quantity) as quantity
    FROM productLink root
    JOIN productLinkPath rootPath ON root.linkId = rootPath.linkId and rootPath.order = 0
    JOIN (SELECT pathId, MAX(`order`) as `order` FROM productLinkPath GROUP BY pathId) leafPathOrder ON rootPath.pathId = leafPathOrder.pathId
    JOIN productLinkPath leafPath ON leafPathOrder.pathId = leafPath.pathId and leafPathOrder.order = leafPath.order
    JOIN productLink leaf ON leafPath.linkId = leaf.linkId
    GROUP BY root.organisationUnitId, root.sku, leaf.sku
) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\\\%'), '_', '\\\\_')
WHERE `item`.`organisationUnitId` = ?
AND IFNULL(productLink.leafSku, `item`.`itemSku`) LIKE ?
AND `item`.`itemQuantity` != 0
AND
    (
        (`item`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding'))
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
    `item`.`itemQuantity` * IFNULL(productLink.quantity, 1) as `itemQuantity`, IF(`item`.`purchaseDate` > `account`.`account`.`cgCreationDate`, 'yes', 'no') AS afterAccountCreation
FROM `item`
JOIN `order` ON (`item`.`orderId` = `order`.`id`)
JOIN `account`.`account` ON (`order`.`accountId` = `account`.`id`)
LEFT JOIN (
    SELECT root.organisationUnitId, root.sku, leaf.sku as leafSku, SUM(leafPath.quantity) as quantity
    FROM productLink root
    JOIN productLinkPath rootPath ON root.linkId = rootPath.linkId and rootPath.order = 0
    JOIN (SELECT pathId, MAX(`order`) as `order` FROM productLinkPath GROUP BY pathId) leafPathOrder ON rootPath.pathId = leafPathOrder.pathId
    JOIN productLinkPath leafPath ON leafPathOrder.pathId = leafPath.pathId and leafPathOrder.order = leafPath.order
    JOIN productLink leaf ON leafPath.linkId = leaf.linkId
    GROUP BY root.organisationUnitId, root.sku, leaf.sku
) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\\\%'), '_', '\\\\_')
WHERE `item`.`organisationUnitId` = ?
AND IFNULL(productLink.leafSku, `item`.`itemSku`) LIKE ?
AND `item`.`itemQuantity` != 0
AND
    (
        (`order`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding'))
        OR
        (`order`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $params = [$result['organisationUnitId'], \CG\Stdlib\escapeLikeValue($result['sku'])];

        $results = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($results);
    }

    protected function areExpectationAndDetailsResultsDifferent(array $result, array $details)
    {
        $count = 0;
        foreach ($details as $detail) {
            $count += $detail['itemQuantity'];
        }
        return ($count != $result['expected']);
    }
}
