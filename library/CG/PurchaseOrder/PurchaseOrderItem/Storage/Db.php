<?php
namespace CG\PurchaseOrder\PurchaseOrderItem\Storage;

use CG\PurchaseOrder\PurchaseOrderItem\Collection;
use CG\PurchaseOrder\PurchaseOrderItem\Entity;
use CG\PurchaseOrder\PurchaseOrderItem\Filter;
use CG\PurchaseOrder\PurchaseOrderItem\StorageInterface;

use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;

use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use CG\Zend\Stdlib\Db\Sql\InsertIgnore;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'purchaseOrderItem';

    public function fetchCollectionByFilter(Filter $filter)
    {

        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

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

        return $query;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select(static::DB_TABLE_NAME);
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert(static::DB_TABLE_NAME);
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update(static::DB_TABLE_NAME);
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete(static::DB_TABLE_NAME);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}
