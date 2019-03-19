<?php
namespace CG\Stock\Storage;

use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\ArrayFiltersToWhereTrait;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use CG\Stock\Collection as StockCollection;
use CG\Stock\Entity as StockEntity;
use CG\Stock\Filter;
use CG\Stock\StorageInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    use ArrayFiltersToWhereTrait;
    use FilterArrayValuesToOrdLikesTrait;

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            return $this->fetchPaginatedCollection(
                new StockCollection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $this->getSelectForFilter($filter),
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getSelectForFilter(Filter $filter)
    {
        $select = $this->getSelect();

        if (!empty($id = $filter->getId())) {
            $select->where->in('id', $id);
        }
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $select->where->in('organisationUnitId', $organisationUnitId);
        }
        if (!empty($locationId = $filter->getLocationId())) {
            $stockLocations = $this->getSelect('stockLocation');
            $stockLocations->where->equalTo('stockLocation.stockId', 'stock.id', Where::TYPE_IDENTIFIER, Where::TYPE_IDENTIFIER);
            $stockLocations->where->in('stockLocation.locationId', $locationId);
            $select->where->expression('EXISTS ?', $stockLocations);
        }
        if (!empty($stockMode = $filter->getStockMode())) {
            $select->where->in('stockMode', $stockMode);
        }
        if ($filter->getIncludePurchaseOrders() !== null) {
            $select->where->equalTo('includePurchaseOrders', (int)$filter->getIncludePurchaseOrders());
        }
        if ($filter->getIncludePurchaseOrdersUseDefault() !== null) {
            $select->where->equalTo('includePurchaseOrdersUseDefault', (int)$filter->getIncludePurchaseOrdersUseDefault());
        }

        if (!empty($sku = $filter->getSku())) {
            // Must do SKU check as (LIKE OR LIKE) instead of IN() otherwise
            // MySQL ignores trailing spaces and we get unexpected results
            $sku = (array) $sku;
            $this->filterArrayValuesToOrdLikes('stock.sku', $sku, $select->where);
        }

        if ($filter->getLowStockThresholdTriggered() !== null) {
            $select->where->equalTo(
                'lowStockThresholdTriggered',
                $filter->getLowStockThresholdTriggered()
            );
        }

        $page = $filter->getPage();
        $limit = $filter->getLimit();
        if ($limit != 'all') {
            $offset = ($page - 1) * $limit;
            $select->limit($limit)->offset($offset);
        }

        return $select;
    }

    public function fetchCollectionByPaginationAndFilters(
        $limit, $page, array $id, array $organisationUnitId, ?array $sku, array $locationId
    ) {
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page, $id, $organisationUnitId, $sku, $locationId)
        );
    }

    public function fetchCollectionBySKUs(array $sku, array $organisationUnitId)
    {
        return $this->fetchCollectionByFilter(
            (new Filter('all', 1))->setSku($sku)->setOrganisationUnitId($organisationUnitId)
        );
    }

    /**
     * @return Select
     */
    protected function getSelect($table = 'stock')
    {
        return $this->getReadSql()->select($table);
    }

    /**
     * @return Insert
     */
    protected function getInsert($table = 'stock')
    {
        return $this->getWriteSql()->insert($table);
    }

    /**
     * @return Update
     */
    protected function getUpdate($table = 'stock')
    {
        return $this->getWriteSql()->update($table);
    }

    /**
     * @return Delete
     */
    protected function getDelete($table = 'stock')
    {
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return StockEntity::class;
    }
}
