<?php
namespace CG\Stock\Command;

use Zend\Db\Sql\Sql;

class FindIncorrectlyAllocatedStock
{
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
WHERE calculatedAllocated {$operator} allocated
ORDER BY organisationUnitId, sku
EOF;

        return $this->sqlClient->getAdapter()->query($query)->execute();
    }
}
