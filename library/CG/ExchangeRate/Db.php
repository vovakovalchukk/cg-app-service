<?php
namespace CG\ExchangeRate\Storage;

use CG\ExchangeRate\Collection;
use CG\ExchangeRate\Entity;
use CG\ExchangeRate\Filter;
use CG\ExchangeRate\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use CG\Zend\Stdlib\Db\Sql\InsertIgnore;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'exchangeRate';

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

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(Entity::getComponentsFromId($entity->getId()));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(Entity::getComponentsFromId($id)),
            $this->getMapper()
        );
    }

    public function save($entity)
    {
        $insert = new InsertIgnore(static::DB_TABLE_NAME);
        $values = $entity->toArray();
        unset($values['id']);
        $insert->values($values);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getDateTo())) {
            $query[static::DB_TABLE_NAME . '.date <= ?'] = $filter->getDateTo();
        }
        if (!empty($filter->getDateFrom())) {
            $query[static::DB_TABLE_NAME . '.date >= ?'] = $filter->getDateFrom();
        }
        if (!empty($filter->getCurrencyCode())) {
            $query[static::DB_TABLE_NAME . '.currencyCode'] = $filter->getCurrencyCode();
        }
        if (!empty($filter->getBaseCurrencyCode())) {
            $query[static::DB_TABLE_NAME . '.baseCurrencyCode'] = $filter->getBaseCurrencyCode();
        }

        return $query;
    }

    protected function toDbArray($entity)
    {
        $data = $entity->toArray();
        unset($data['id']);
        return $data;
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