<?php
namespace CG\Stock\Audit\Combined\Storage;

use CG\Stdlib;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Mapper\FromArrayInterface as ArrayMapper;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stock\Audit\Combined\Collection;
use CG\Stock\Audit\Combined\Entity as StockLog;
use CG\Stock\Audit\Combined\Filter as Filter;
use CG\Stock\Audit\Combined\StorageInterface;
use CG\Stock\Audit\Combined\Type;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

class Db extends DbAbstract implements StorageInterface
{
    protected const DEFAULT_SORT_DIR = 'ASC';
    protected const TABLE_STOCK_LOG = 'stockLog';
    protected const TABLE_STOCK_ADJUSTMENT_LOG = 'stockAdjustmentLog';
    protected const TABLE_STOCK_ADJUSTMENT_LOG_RELATED = 'stockAdjustmentLogRelated';
    protected const INCLUDE_RELATED = [
        self::TABLE_STOCK_LOG => false,
        self::TABLE_STOCK_ADJUSTMENT_LOG => true,
    ];

    /** @var Sql */
    protected $listingReadSql;

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

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Sql $listingReadSql, ArrayMapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
        $this->listingReadSql = $listingReadSql;
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->getFilteredSelect($filter);

            $this->applySorting($select, $filter)
                ->applyPagination($select, $filter);

            $collection = $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }

        $this->appendListingUrls($collection);
        return $collection;
    }

    protected function getFilteredSelect(Filter $filter)
    {
        if (count($filter->getType()) == 1) {
            if ($filter->getType()[0] == Type::ADJUSTMENT) {
                $stockAdjustmentSelect = $this->getStockAdjustmentLogSelect();
                $stockAdjustmentSelect->where($this->buildFilterQuery($filter, static::TABLE_STOCK_ADJUSTMENT_LOG));
                return $stockAdjustmentSelect;
            }
            $stockLogSelect = $this->getStockLogSelect();
            $stockLogSelect->where($this->buildFilterQuery($filter, static::TABLE_STOCK_LOG));
            return $stockLogSelect;
        }

        $stockLogSelect = $this->getStockLogSelect();
        $stockAdjustmentSelect = $this->getStockAdjustmentLogSelect();
        $stockLogSelect->where($this->buildFilterQuery($filter, static::TABLE_STOCK_LOG));
        $stockAdjustmentSelect->where($this->buildFilterQuery($filter, static::TABLE_STOCK_ADJUSTMENT_LOG));
        return $this->getCombinedSelect($stockLogSelect, $stockAdjustmentSelect);
    }

    protected function buildFilterQuery(Filter $filter, $tableName)
    {
        $query = [];
        if (!empty($filter->getOrganisationUnitId())) {
            $query = array_merge($query, $this->getOrganisationUnitIdQuery($filter->getOrganisationUnitId(), $tableName));
        }
        if (!empty($filter->getSku())) {
            $query = array_merge($query, $this->getSkuQuery($filter->getSku(), $tableName));
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

    protected function getOrganisationUnitIdQuery(array $organisationUnitId, string $tableName): array
    {
        if (static::INCLUDE_RELATED[$tableName]) {
            return [$this->getAdjustmentLogRelatedQuery($organisationUnitId, $tableName, 'organisationUnitId')];
        }
        return [$tableName.'.organisationUnitId' => $organisationUnitId];
    }

    protected function getSkuQuery(array $sku, string $tableName): array
    {
        if (static::INCLUDE_RELATED[$tableName]) {
            return [$this->getAdjustmentLogRelatedQuery($sku, $tableName, 'sku')];
        }
        return [$tableName.'.sku' => $sku];
    }

    protected function getAdjustmentLogRelatedQuery(array $filterValues, string $tableName, string $columnName): Predicate
    {
        return (new Predicate())
            ->in($tableName . '.' . $columnName, $filterValues)
            ->or
            ->in(static::TABLE_STOCK_ADJUSTMENT_LOG_RELATED . '.' . $columnName, $filterValues);
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

    protected function getCombinedSelect(Select $stockLogSelect, Select $stockAdjustmentSelect): Select
    {
        $stockLogSelect->combine($stockAdjustmentSelect);
        return $this->getReadSql()->select(['sl' => $stockLogSelect]);
    }

    protected function getStockLogSelect(): Select
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
            'accountName' => new Expression('null'),
        ]);
        return $select;
    }

    protected function getStockAdjustmentLogSelect(): Select
    {
        $select = $this->getReadSql()->select('stockAdjustmentLog');
        $select->columns([
            // Columns common to both tables
            'type' => new Expression("'" . Type::ADJUSTMENT . "'"),
            'id' => new Expression('IFNULL(stockAdjustmentLogRelated.id, stockAdjustmentLog.id)'),
            'date', 'time', 'dateTime' => new Expression("CONCAT(`date`, ' ', `time`)"), 'itid', 'organisationUnitId',
            'sku' => new Expression('IF(stockAdjustmentLogRelated.id IS NULL, stockAdjustmentLog.sku, stockAdjustmentLogRelated.sku)'),
            // Columns only present on stockAdjustmentLog
            'stid', 'action', 'accountId', 'stockManagement',
            'listingId', 'productId', 'itemStatus', 'listingStatus',
            'adjustmentType' => 'type', 'adjustmentOperator' => 'operator',// 'adjustmentQty' => 'quantity',
            'adjustmentQty' => new Expression('IF(stockAdjustmentLogRelated.id IS NULL, stockAdjustmentLog.quantity, stockAdjustmentLogRelated.quantity)'),
            'referenceSku' => new Expression('IF(stockAdjustmentLogRelated.id IS NOT NULL, stockAdjustmentLog.sku, null)'),
            'adjustmentReferenceQuantity' => new Expression('IF(stockAdjustmentLogRelated.id IS NOT NULL, stockAdjustmentLog.quantity, null)'),
            //'referenceSku', 'adjustmentReferenceQuantity' => 'referenceQuantity',
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
            'stockAdjustmentLogRelated',
            'stockAdjustmentLog.id = stockAdjustmentLogRelated.stockAdjustmentLogId',
            [],
            Select::JOIN_LEFT
        );
        return $select;
    }

    protected function appendListingUrls(Collection $collection): void
    {
        $listingIds = array_filter($collection->getArrayOf('listingId'));
        if (empty($listingIds)) {
            return;
        }

        $listingUrls = $this->getListingUrlMap($listingIds);

        /** @var StockLog $entity */
        foreach ($collection as $entity) {
            $listingId = $entity->getListingId();
            if (isset($listingUrls[$listingId])) {
                $entity->setListingUrl($listingUrls[$listingId]);
            }
        }
    }

    protected function getListingUrlMap($listingIds): array
    {
        $select = $this->getListingUrlSelect($listingIds);
        $results = $this->listingReadSql->prepareStatementForSqlObject($select)->execute();

        $listingUrls = [];
        foreach ($results as $result) {
            $listingUrls[$result['id']] = $result['listingUrl'];
        }
        return $listingUrls;
    }

    protected function getListingUrlSelect(array $listingIds): Select
    {
        return $this->listingReadSql->select('listing')->columns(['id', 'listingUrl' => 'url'])->where(['id' => $listingIds]);
    }

    public function getEntityClass()
    {
        return StockLog::class;
    }
}