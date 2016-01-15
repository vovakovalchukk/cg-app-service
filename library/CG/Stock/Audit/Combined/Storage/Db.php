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
        'id', 'itid', 'action', 'accountName', 'orderExternalId', 'stid',
    ];

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

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

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getOrganisationUnitId())) {
            $query['organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getSku())) {
            $query['sku'] = $filter->getSku();
        }
        if (!empty($filter->getItemStatus())) {
            $query['itemStatus'] = $filter->getItemStatus();
        }
        if (!empty($filter->getDateTimeFrom())) {
            $query[] = 'dateTime >= \'' . $filter->getDateTimeFrom() . '\'';
        }
        if (!empty($filter->getDateTimeTo())) {
            $query[] = 'dateTime <= \'' . $filter->getDateTimeTo() . '\'';
        }
        if (count($filter->getType()) == 1) {
            $query['type'] = $filter->getType();
        }
        if (!empty($filter->getSearchTerm())) {
            $query = array_merge($query, $this->getSearchTermQuery($filter->getSearchTerm()));
        }
        return $query;
    }

    protected function getSearchTermQuery($searchTerm)
    {
        $searchFields = [];
        $searchTerm  = "%" . Stdlib\escapeLikeValue($searchTerm) . "%";

        foreach ($this->searchFields as $field) {
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

    protected function getSelect()
    {
        $stockLogSelect = $this->getStockLogSelect();
        $stockLogSelect->combine($this->getStockAdjustmentLogSelect());

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
            // Columns only present on stockLog
            'stockId', 'locationId', 'allocatedQty', 'onHandQty',
            // Columns from joined tables
            'accountName' => new Expression('null'), 'orderId' => new Expression('null'), 
            'orderExternalId' => new Expression('null'), 'listingUrl' => new Expression('null'),
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
            // Columns only present on stockLog
            'stockId' => new Expression('null'), 'locationId' => new Expression('null'),
            'allocatedQty' => new Expression('null'), 'onHandQty' => new Expression('null'),
        ]);
        // Columns from joined tables
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
            'order',
            'order.id = item.orderId OR (order.id = stockAdjustmentLog.stid AND order.organisationUnitId = stockAdjustmentLog.organisationUnitId)',
            ['orderId' => 'id', 'orderExternalId' => 'externalId'],
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