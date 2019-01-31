<?php
namespace CG\Stock\Audit\Combined\Storage;

use CG\Stock\Audit\Combined\Collection;
use CG\Stock\Audit\Combined\Entity as StockLog;
use CG\Stock\Audit\Combined\Filter as Filter;
use CG\Stock\Audit\Combined\StorageInterface;
use CG\Stock\Audit\Combined\Type;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\TableIdentifier;

class Db extends DbAbstract implements StorageInterface
{
    const DEFAULT_SORT_DIR = 'ASC';

    protected $sortByMap = [
        'dateTime' => 'getSortByDateTime'
    ];
    protected $searchFields = [
        'stockLog' => [
            'stockLog.id', 'stockLog.itid',
        ],
        'stockAdjustmentLog' => [
            'stockAdjustmentLog.id', 'stockAdjustmentLog.itid', 'stockAdjustmentLog.action',
            'account.account.displayName', 'itemOrder.externalId', 'orderDirect.externalId', 'stockAdjustmentLog.stid',
        ]
    ];

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->getFilteredSelect($filter);

            $this->applySorting($select, $filter)
                ->applyPagination($select, $filter);

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getFilteredSelect(Filter $filter)
    {
        if (count($filter->getType()) == 1) {
            if ($filter->getType()[0] == Type::ADJUSTMENT) {
                $stockAdjustmentSelect = $this->getStockAdjustmentLogSelect();
                $stockAdjustmentSelect->where($this->buildFilterQuery($filter, 'stockAdjustmentLog'));
                return $stockAdjustmentSelect;
            }
            $stockLogSelect = $this->getStockLogSelect();
            $stockLogSelect->where($this->buildFilterQuery($filter, 'stockLog'));
            return $stockLogSelect;
        }

        $stockLogSelect = $this->getStockLogSelect();
        $stockAdjustmentSelect = $this->getStockAdjustmentLogSelect();
        $stockLogSelect->where($this->buildFilterQuery($filter, 'stockLog'));
        $stockAdjustmentSelect->where($this->buildFilterQuery($filter, 'stockAdjustmentLog'));
        return $this->getCombinedSelect($stockLogSelect, $stockAdjustmentSelect);
    }

    protected function buildFilterQuery(Filter $filter, $tableName)
    {
        $query = [];
        if (!empty($filter->getOrganisationUnitId())) {
            $query[$tableName.'.organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getSku())) {
            $query[$tableName.'.sku'] = $filter->getSku();
        }
        if (!empty($filter->getItemStatus())) {
            $query = array_merge($query, $this->getItemStatusQuery($filter->getItemStatus(), $tableName));
        }
        if (!empty($filter->getDateTimeFrom())) {
            list($date, $time) = explode(' ', $filter->getDateTimeFrom());
            $query['(('.$tableName.'.date >= ? AND '.$tableName.'.time >= ?) OR '.$tableName.'.date > ?)'] = [$date, $time, $date];
        }
        if (!empty($filter->getDateTimeTo())) {
            list($date, $time) = explode(' ', $filter->getDateTimeTo());
            $query['(('.$tableName.'.date <= ? AND '.$tableName.'.time <= ?) OR '.$tableName.'.date < ?)'] = [$date, $time, $date];
        }
        if (!empty($filter->getSearchTerm())) {
            $query = array_merge($query, $this->getSearchTermQuery($filter->getSearchTerm(), $tableName));
        }
        return $query;
    }

    protected function getItemStatusQuery(array $itemStatus, $tableName)
    {
        // Item statuses only accessible via stockAdjustmentLog
        if ($tableName == 'stockLog') {
            return [$tableName.'.id' => -1];
        }
        return [$tableName.'.itemStatus' => $itemStatus];
    }

    protected function getSearchTermQuery($searchTerm, $tableName)
    {
        $searchFields = [];
        $searchTerm  = "%" . Stdlib\escapeLikeValue($searchTerm) . "%";

        foreach ($this->searchFields[$tableName] as $field) {
            $searchFields[] = $field . ' LIKE ?';
        }

        return [
            "(" . implode(' OR ', $searchFields) . ")" => array_fill(0, count($searchFields), $searchTerm)
        ];
    }

    protected function applySorting(Select $select, Filter $filter)
    {
        if (!$filter->getSortBy()) {
            return $this;
        }
        $sortDir = ($filter->getSortDirection() ?: static::DEFAULT_SORT_DIR);
        if (isset($this->sortByMap[$filter->getSortBy()])) {
            $method = $this->sortByMap[$filter->getSortBy()];
            $sort = call_user_func([$this, $method], $sortDir);
        } else {
            $sort = $filter->getSortBy() . ' ' . $sortDir;
        }
        $select->order($sort);

        return $this;
    }

    protected function getSortByDateTime($sortDir)
    {
        return 'date ' . $sortDir . ', time ' . $sortDir;
    }

    protected function applyPagination(Select $select, Filter $filter)
    {
        if ($filter->getLimit() == 'all' || $filter->getLimit() == null) {
            return $this;
        }
        $offset = ($filter->getPage() - 1) * $filter->getLimit();
        $select->limit($filter->getLimit())
            ->offset($offset);

        return $this;
    }

    protected function getCombinedSelect(Select $stockLogSelect, Select $stockAdjustmentSelect)
    {
        $stockLogSelect->combine($stockAdjustmentSelect);
        return $this->getReadSql()->select(['sl' => $stockLogSelect]);
    }

    protected function getStockLogSelect()
    {
        $select = $this->getReadSql()->select('stockLog');
        $select->columns([
            // Columns common to both tables
            'type' => new Expression("'" . Type::LOG . "'"), 'id', 'date', 'time',
            'dateTime' => new Expression("CONCAT(`date`, ' ', `time`)"), 'itid', 'organisationUnitId', 'sku',
            // Columns only present on stockAdjustmentLog
            'stid' => new Expression('null'), 'action' => new Expression("'Stock Log'"), 'accountId' => new Expression('null'), 
            'stockManagement' => new Expression('null'), 
            'listingId' => new Expression('null'), 'productId' => new Expression('null'), 'itemStatus' => new Expression('null'), 
            'listingStatus' => new Expression('null'), 'adjustmentType' => new Expression('null'), 
            'adjustmentOperator' => new Expression('null'), 'adjustmentQty' => new Expression('null'),
            'referenceSku' => new Expression('null'), 'adjustmentReferenceQuantity' => new Expression('null'),
            // Columns only present on stockLog
            'stockId', 'locationId', 'allocatedQty', 'onHandQty',
            // Columns from joined tables
            'orderId' => new Expression('null'), 'orderExternalId' => new Expression('null'),
            'accountName' => new Expression('null'), 'listingUrl' => new Expression('null'),
        ]);
        return $select;
    }

    protected function getStockAdjustmentLogSelect()
    {
        $select = $this->getReadSql()->select('stockAdjustmentLog');
        $select->columns([
            // Columns common to both tables
            'type' => new Expression("'" . Type::ADJUSTMENT . "'"), 'id', 'date', 'time', 'dateTime' => new Expression("CONCAT(`date`, ' ', `time`)"), 'itid', 'organisationUnitId', 'sku',
            // Columns only present on stockAdjustmentLog
            'stid', 'action', 'accountId', 'stockManagement',
            'listingId', 'productId', 'itemStatus', 'listingStatus',
            'adjustmentType' => 'type', 'adjustmentOperator' => 'operator', 'adjustmentQty' => 'quantity',
            'referenceSku', 'adjustmentReferenceQuantity' => 'referenceQuantity',
            // Columns only present on stockLog
            'stockId' => new Expression('null'), 'locationId' => new Expression('null'),
            'allocatedQty' => new Expression('null'), 'onHandQty' => new Expression('null'),
           // Columns from joined tables
            'orderId' => new Expression('IFNULL(itemOrder.id, orderDirect.id)'), 'orderExternalId' => new Expression('IFNULL(itemOrder.externalId, orderDirect.externalId)'),
        ]);
        $select->join(
            new TableIdentifier('account', 'account'),
            'account.account.id = stockAdjustmentLog.accountId',
            ['accountName' => 'displayName'],
            Select::JOIN_LEFT
        );
        $select->join(
            'item',
            'item.id = stockAdjustmentLog.stid AND item.organisationUnitId = stockAdjustmentLog.organisationUnitId',
            [],
            Select::JOIN_LEFT
        );
        $select->join(
            ['itemOrder' => 'order'],
            'itemOrder.id = item.orderId',
            [],
            Select::JOIN_LEFT
        );
        $select->join(
            ['orderDirect' => 'order'],
            'orderDirect.id = stockAdjustmentLog.stid AND orderDirect.organisationUnitId = stockAdjustmentLog.organisationUnitId',
            [],
            Select::JOIN_LEFT
        );
        $select->join(
            'listing',
            'listing.id = stockAdjustmentLog.listingId',
            ['listingUrl' => 'url'],
            Select::JOIN_LEFT
        );
        return $select;
    }

    public function getEntityClass()
    {
        return StockLog::class;
    }
}