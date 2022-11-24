<?php

namespace CG\Settings\Shipping\Alias\Rule\Storage;

use CG\Settings\Shipping\Alias\Rule\Collection;
use CG\Settings\Shipping\Alias\Rule\Entity;
use CG\Settings\Shipping\Alias\Rule\Filter;
use CG\Settings\Shipping\Alias\Rule\Mapper;
use CG\Settings\Shipping\Alias\Rule\StorageInterface;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Db\DeadlockAwareSaveTrait;
use CG\Stdlib\Storage\Db\Zend\TransactionTrait;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\Sql as ZendSql;

class Db implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;
    use DeadlockAwareSaveTrait;
    use TransactionTrait;

    protected const DB_TABLE_NAME = 'aliasRule';

    protected $readSql;
    protected $fastReadSql;
    protected $writeSql;
    protected $mapper;

    public function __construct(ZendSql $readSql, ZendSql $fastReadSql, ZendSql $writeSql, Mapper $mapper)
    {
        $this
            ->setReadSql($readSql)
            ->setFastReadSql($fastReadSql)
            ->setWriteSql($writeSql)
            ->setMapper($mapper);
    }

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

    protected function fetchPaginatedCollection(
        CollectionInterface $collection,
        ZendSql $sql,
        ZendSelect $select,
        Mapper $mapper, $expected = false)
    {
        $collection->setTotal($this->countResults($sql, $select));
        return $this->fetchCollection($collection, $sql, $select, $mapper, $expected);
    }

    protected function fetchCollection(
        CollectionInterface $collection,
        ZendSql $sql,
        ZendSelect $select,
        Mapper $mapper, $expected = false)
    {
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0 || ($expected !== false && $results->count() != $expected)) {
            throw new NotFound();
        }

        foreach ($results as $entityData) {
            $collection->attach($mapper->fromArray($entityData));
        }

        return $collection;
    }

    protected function fetchEntity(ZendSql $sql, ZendSelect $select, Mapper $mapper)
    {
        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();
        if ($results->count() != 1) {
            throw new NotFound();
        }

        return $mapper->fromArray($results->current());
    }

    public function fetch($id, $shippingAliasId)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'id' => $id,
                'shippingAliasId' => $shippingAliasId
            )),
            $this->getMapper()
        );
    }

    protected function countResults(ZendSql $sql, ZendSelect $select)
    {
        $count = clone $select;

        $countBy = '*';
        $groups = $count->getRawState('group');
        if (! empty($groups)) {
            $countBy = 'DISTINCT '.implode(',',$groups);
        }

        $countExpression = new Expression('COUNT('.$countBy.')');
        $count
            ->columns(array_merge($select->getRawState(ZendSelect::COLUMNS), ['count' => $countExpression]))
            ->reset('limit')
            ->reset('offset')
            ->reset('order')
            ->reset('group');

        $countResults = $sql->prepareStatementForSqlObject($count)->execute();

        $total = $countResults->current();
        return $total['count'];
    }

    protected function saveEntity($entity)
    {
        if ($entity->getId() != null) {
            $this->updateEntity($entity);
        } else {
            $this->insertEntity($entity);
        }
        return $entity;
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))
            ->where(array('id' => $entity->getId(), 'shippingAliasId' => $entity->getShippingAliasId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        $entity->setId($id);
        $entity->setNewlyInserted(true);
    }

    protected function getEntityArray($entity)
    {
        return $entity->toArray();
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'id' => $entity->getId(),
            'shippingAliasId' => $entity->getShippingAliasId()
        ));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function buildFilterQuery(Filter $filter): array
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

    public function setMapper(Mapper $mapper): Db
    {
        $this->mapper = $mapper;
        return $this;
    }

    protected function getMapper()
    {
        return $this->mapper;
    }

    public function setFastReadSql(ZendSql $fastReadSql): Db
    {
        $this->fastReadSql = $fastReadSql;
        return $this;
    }

    protected function getFastReadSql()
    {
        return $this->fastReadSql;
    }

    public function setReadSql(ZendSql $readSql): Db
    {
        $this->readSql = $readSql;
        return $this;
    }

    protected function getReadSql()
    {
        return $this->readSql;
    }

    public function setWriteSql(ZendSql $writeSql): Db
    {
        $this->writeSql = $writeSql;
        return $this;
    }

    public function getWriteSql()
    {
        return $this->writeSql;
    }
}