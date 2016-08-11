<?php
namespace CG\Settings\Order\Storage;

use CG\Settings\Order\Collection;
use CG\Settings\Order\Entity;
use CG\Settings\Order\Filter;
use CG\Settings\Order\Mapper;
use CG\Settings\Order\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Db\DeadlockAwareSaveTrait;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use CG\Stdlib\Storage\Db\Zend\TransactionTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;

class Db implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;
    use SqlStorage;
    use DeadlockAwareSaveTrait;
    use TransactionTrait;

    const TABLE = 'orderSettings';

    /** @var Sql $readSql */
    protected $readSql;
    /** @var Sql $fastReadSql */
    protected $fastReadSql;
    /** @var Sql $writeSql */
    protected $writeSql;
    /** @var Mapper $mapper */
    protected $mapper;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        $this->setReadSql($readSql)->setFastReadSql($fastReadSql)->setWriteSql($writeSql)->setMapper($mapper);
    }

    /**
     * @return Entity
     */
    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->readSql,
            $this->getSelect()->where([
                'id' => $id
            ]),
            $this->mapper
        );
    }

    protected function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getId());
            return $this->updateEntity($entity);
        } catch (NotFound $exception) {
            return $this->insertEntity($entity);
        }
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($entity->toArray());
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        $entity->setNewlyInserted(true);
        return $entity;
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($entity->toArray())->where(['id' => $entity->getId()]);
        $this->writeSql->prepareStatementForSqlObject($update)->execute();
        return $entity;
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(['id' => $entity->getId()]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    /**
     * @return Collection
     */
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new Collection(Entity::class, __FUNCTION__, $filter->toArray()),
                $this->readSql,
                $select,
                $this->mapper
            );

        } catch (ExceptionInterface $exception) {
            throw new StorageException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['orderSettings.id'] = $filter->getId();
        }
        if (!empty($filter->getAutoArchiveTimeframe())) {
            $query['orderSettings.autoArchiveTimeframe'] = $filter->getAutoArchiveTimeframe();
        }
        return $query;
    }

    /**
     * @return self
     */
    protected function setReadSql(Sql $readSql)
    {
        $this->readSql = $readSql;
        return $this;
    }

    /**
     * @return self
     */
    protected function setFastReadSql(Sql $fastReadSql)
    {
        $this->fastReadSql = $fastReadSql;
        return $this;
    }

    /**
     * @return self
     */
    protected function setWriteSql(Sql $writeSql)
    {
        $this->writeSql = $writeSql;
        return $this;
    }

    /**
     * @return Sql
     */
    protected function getWriteSql()
    {
        return $this->writeSql;
    }

    /**
     * @return self
     */
    protected function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->readSql->select(static::TABLE);
    }

    /**
     * @return Insert
     */
    protected function getInsert()
    {
        return $this->writeSql->insert(static::TABLE);
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->writeSql->update(static::TABLE);
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->writeSql->delete(static::TABLE);
    }
}
