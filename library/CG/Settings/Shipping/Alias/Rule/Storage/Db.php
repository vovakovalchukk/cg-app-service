<?php

namespace CG\Settings\Shipping\Alias\Rule\Storage;

use CG\Settings\Shipping\Alias\Rule\Collection;
use CG\Settings\Shipping\Alias\Rule\Entity;
use CG\Settings\Shipping\Alias\Rule\Filter;
use CG\Settings\Shipping\Alias\Rule\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    protected const DB_TABLE_NAME = 'aliasRule';

    public function fetchCollectionByFilter(Filter $filter): Collection
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);
            if (!empty($filter->getOrderBy()) && !empty($filter->getOrderDirection())) {
                $select->order($filter->getOrderBy() . ' ' . $filter->getOrderDirection());
            }
            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())->offset($offset);
            }
            $collection = new Collection(Entity::class, __FUNCTION__, $filter->toArray());
            $this->fetchPaginatedCollection(
                $collection,
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
            return $collection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = array_filter($filter->toArray());
        unset($query['limit'], $query['page'], $query['orderBy'], $query['orderDirection']);
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
}