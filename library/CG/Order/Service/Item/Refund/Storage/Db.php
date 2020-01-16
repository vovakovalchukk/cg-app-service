<?php
namespace CG\Order\Service\Item\Refund\Storage;

use CG\Order\Shared\Item\Refund\Collection;
use CG\Order\Shared\Item\Refund\Entity;
use CG\Order\Shared\Item\Refund\Filter;
use CG\Order\Shared\Item\Refund\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    protected const DB_TABLE_NAME = 'orderItemRefund';

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
        $query = array_filter($filter->toArray());
        unset($query['limit'], $query['page']);
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