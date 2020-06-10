<?php
namespace CG\Stock\Command;

use CG\Access\BulkAccessService;
use CG\Cilex\ModulusAwareInterface;
use CG\Cilex\ModulusTrait;
use CG\OrganisationUnit\Collection as OrganisationUnits;
use CG\OrganisationUnit\Filter as OrganisationUnitFilter;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Sql;

class FindIncorrectlyAllocatedStock implements LoggerAwareInterface, ModulusAwareInterface
{
    use LogTrait;
    use ModulusTrait;

    const LOG_CODE = 'FindIncorrectlyAllocatedStockCommand';
    const LOG_FINDINGS = 'FindIncorrectlyAllocatedStock command found OU %d, SKU %s had %d allocated but we expected %d. Details of expectation:';
    const LOG_FINDINGS_ORDERS = 'Heres the same results but (for OU %d, SKU %s) based on order status instead of item status:';

    /** @var Sql */
    protected $sqlClient;
    /** @var OrganisationUnitService */
    protected $organisationUnitService;
    /** @var BulkAccessService */
    protected $bulkAccessService;

    public function __construct(
        Sql $sqlClient,
        OrganisationUnitService $organisationUnitService,
        BulkAccessService $bulkAccessService
    ) {
        $this->sqlClient = $sqlClient;
        $this->organisationUnitService = $organisationUnitService;
        $this->bulkAccessService = $bulkAccessService;
    }

    public function findOverAllocated(int $organisationUnitId = null, string $sku = null): iterable
    {
        return $this->findIncorrectlyAllocated('>', $organisationUnitId, $sku);
    }

    public function findUnderAllocated(int $organisationUnitId = null, string $sku = null): iterable
    {
        return $this->findIncorrectlyAllocated('<', $organisationUnitId, $sku);
    }

    public function findIncorrectlyAllocated(string $operator = null, int $organisationUnitId = null, string $sku = null): iterable
    {
        $operator = $operator ?? '!=';

        $organisationUnits = $this->getOrganisationUnits($organisationUnitId);
        if (is_null($organisationUnitId) && is_null($sku)) {
            $this->filterCollection($organisationUnits);
        }
        $organisationUnitIds = $this->getActiveOrganisationUnitIds($organisationUnits);
        if (empty($organisationUnitIds)) {
            $this->logNotice('No active organisation units to process - exiting', [], static::LOG_CODE);
            return [];
        }
        $organisationUnitIdString = implode(',', $organisationUnitIds);

        $skuWhere = "";
        if ($sku !== null) {
            $sku = \CG\Stdlib\escapeLikeValue($sku);
            $skuWhere = "AND s.sku LIKE '$sku'";
        }

        $query = <<<EOF
SELECT s.sku, s.organisationUnitId, IFNULL(calculatedAllocated, 0) as expected, sl.allocated as actual, IFNULL(calculatedAllocated, 0) - allocated as diff, IFNULL(unknownOrders, 0) as unknownOrders
FROM stock AS s
INNER JOIN stockLocation AS sl ON s.id = sl.stockId AND sl.type = 'real'
INNER JOIN location AS l ON sl.locationId = l.id AND l.type = 'Merchant'
LEFT JOIN (
    SELECT IFNULL(productLink.leafSku, item.itemSku) as allocatedSku, order.rootOrganisationUnitId, SUM(
		IF(item.purchaseDate > account.cgCreationDate,
			IF(item.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding', 'cancel failed', 'dispatch failed'), item.itemQuantity * IFNULL(productLink.quantity, 1), 0),
			IF(item.`status` IN ('awaiting payment', 'new'), item.itemQuantity * IFNULL(productLink.quantity, 1), 0)
		)) as calculatedAllocated,
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
		WHERE root.organisationUnitId IN ({$organisationUnitIdString})
		GROUP BY root.organisationUnitId, root.sku, leaf.sku
    ) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\%'), '_', '\\_')
	WHERE item.itemSku != ''
	AND item.stockManaged = 1
	AND item.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding', 'cancel failed', 'dispatch failed',  'unknown')
	AND account.rootOrganisationUnitId IN ({$organisationUnitIdString})
	GROUP BY allocatedSku, order.rootOrganisationUnitId
) as calc ON (
    calc.allocatedSku LIKE REPLACE(REPLACE(REPLACE(s.sku, '\\\\', '\\\\\\\\'), '%', '\\%'), '_', '\\_')
    AND s.organisationUnitId = calc.rootOrganisationUnitId
)
WHERE allocated {$operator} IFNULL(calculatedAllocated, 0)
AND s.organisationUnitId IN ({$organisationUnitIdString})
{$skuWhere}
ORDER BY s.organisationUnitId, sku
EOF;
        $results = $this->sqlClient->getAdapter()->query($query)->execute();
        $this->logFindings($results);

        return $results;
    }

    protected function logFindings(ResultInterface $results): void
    {
        foreach ($results as $result) {
            $details = $this->getExpectedAllocatedDetails($result);
            $discrepency = $this->areExpectationAndDetailsResultsDifferent($result, $details);
            $this->logDebugDump($details, static::LOG_FINDINGS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku'], $result['actual'], $result['expected']], static::LOG_CODE, ['ticket' => 'CGIV-7567', 'discrepency' => $discrepency]);

            $detailsByOrder = $this->getExpectedAllocatedDetailsByOrderStatus($result);
            $this->logDebugDump($detailsByOrder, static::LOG_FINDINGS_ORDERS, ['ou' => $result['organisationUnitId'], 'sku' => $result['sku']], static::LOG_CODE, ['ticket' => 'CGIV-7567']);
        }
    }

    protected function getExpectedAllocatedDetails(array $result): array
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
    JOIN (
        SELECT pathId, MAX(`order`) as `order` 
        FROM productLinkPath
        JOIN productLink root ON root.`linkId` = productLinkPath.`linkId`
        WHERE root.`organisationUnitId` =  ?
        AND root.`sku` LIKE ? 
        GROUP BY pathId
    ) leafPathOrder ON rootPath.pathId = leafPathOrder.pathId
    JOIN productLinkPath leafPath ON leafPathOrder.pathId = leafPath.pathId and leafPathOrder.order = leafPath.order
    JOIN productLink leaf ON leafPath.linkId = leaf.linkId
    GROUP BY root.organisationUnitId, root.sku, leaf.sku
) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\%'), '_', '\\_')
WHERE `item`.`organisationUnitId` = ?
AND `account`.`rootOrganisationUnitId` = ? 
AND IFNULL(productLink.leafSku, `item`.`itemSku`) LIKE ?
AND `item`.`itemQuantity` != 0
AND
    (
        (`item`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding', 'cancel failed', 'dispatch failed'))
        OR
        (`item`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `item`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $ouId = $result['organisationUnitId'];
        $sku = \CG\Stdlib\escapeLikeValue($result['sku']);
        $params = [$ouId, $sku, $ouId, $ouId, $sku];

        $secondaryResults = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($secondaryResults);
    }

    protected function getExpectedAllocatedDetailsByOrderStatus(array $result): array
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
    JOIN (
        SELECT pathId, MAX(`order`) as `order` 
        FROM productLinkPath
        JOIN productLink root ON root.`linkId` = productLinkPath.`linkId`
        WHERE root.`organisationUnitId` =  ?
        AND root.`sku` LIKE ? 
        GROUP BY pathId
    ) leafPathOrder ON rootPath.pathId = leafPathOrder.pathId
    JOIN productLinkPath leafPath ON leafPathOrder.pathId = leafPath.pathId and leafPathOrder.order = leafPath.order
    JOIN productLink leaf ON leafPath.linkId = leaf.linkId
    GROUP BY root.organisationUnitId, root.sku, leaf.sku
) AS productLink ON order.rootOrganisationUnitId = productLink.organisationUnitId AND item.itemSku LIKE REPLACE(REPLACE(REPLACE(productLink.sku, '\\\\', '\\\\\\\\'), '%', '\\%'), '_', '\\_')
WHERE `item`.`organisationUnitId` = ?
AND `account`.`rootOrganisationUnitId` = ? 
AND IFNULL(productLink.leafSku, `item`.`itemSku`) LIKE ?
AND `item`.`itemQuantity` != 0
AND
    (
        (`order`.`purchaseDate` > `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new', 'cancelling', 'dispatching', 'refunding', 'cancel failed', 'dispatch failed'))
        OR
        (`order`.`purchaseDate` <= `account`.`account`.`cgCreationDate` AND `order`.`status` IN ('awaiting payment', 'new'))
    )
EOF;
        $ouId = $result['organisationUnitId'];
        $sku = \CG\Stdlib\escapeLikeValue($result['sku']);
        $params = [$ouId, $sku, $ouId, $ouId, $sku];

        $results = $this->sqlClient->getAdapter()->query($query, $params);
        return iterator_to_array($results);
    }

    protected function areExpectationAndDetailsResultsDifferent(array $result, array $details): bool
    {
        $count = 0;
        foreach ($details as $detail) {
            $count += $detail['itemQuantity'];
        }
        return ($count != $result['expected']);
    }

    protected function getOrganisationUnits(?int $organisationUnitId = null): OrganisationUnits
    {
        if ($organisationUnitId === null) {
            return $this->organisationUnitService->fetchRootOus('all', 1);
        }
        return $this->organisationUnitService->fetchCollectionByFilter(
            (new OrganisationUnitFilter(1, 1))
                ->setId([$organisationUnitId])
        );
    }

    protected function getActiveOrganisationUnitIds(OrganisationUnits $organisationUnits): array
    {
        $organisationUnitSystemAccess = $this->bulkAccessService->getSystemAccess($organisationUnits);
        return array_keys(array_filter($organisationUnitSystemAccess));
    }
}
