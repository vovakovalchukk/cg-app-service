<?php
namespace CG\Product\Category\ExternalData\Storage;

use CG\Product\Category\ExternalData\BuilderInterface;
use CG\Product\Category\ExternalData\BuilderFactory;
use CG\Product\Category\ExternalData\Collection;
use CG\Product\Category\ExternalData\Entity;
use CG\Product\Category\ExternalData\Filter;
use CG\Product\Category\ExternalData\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Mapper\FromArrayInterface as ArrayMapper;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;

/**
 * Class Db
 * @package CG\Product\Category\ExternalData\Storage
 * @method Sql getReadSql()
 * @method Sql getWriteSql()
 */
class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'categoryExternal';

    /** @var  BuilderFactory */
    protected $builderFactory;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, ArrayMapper $mapper, BuilderFactory $channelFactory)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
        $this->builderFactory = $channelFactory;
    }

    public function fetch($id)
    {
        /** @var Entity $entity */
        $entity = $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(['categoryId' => $id]),
            $this->getMapper()
        );
        $entity->setData($this->getBuilderForEntity($entity)->fetch($entity));
        return $entity;
    }

    /**
     * @param Entity $entity
     */
    public function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getCategoryId());
            $this->updateEntity($entity);
        } catch (NotFound $e) {
            $this->insertEntity($entity);
        }
    }

    /**
     * @param Entity $entity
     */
    public function remove($entity)
    {
        parent::remove($entity);
        $this->getBuilderForEntity($entity)->remove($entity->getData());
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);
            $this->setQueryOffsetAndLimit($filter, $select);
            return $this->fetchFilteredCollection($filter, $select);
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $filterArray = $filter->toArray();
        unset($filterArray['limit'], $filterArray['page']);
        return array_filter($filterArray);
    }

    protected function setQueryOffsetAndLimit(Filter $filter, Select $select): void
    {
        if ($filter->getLimit() == 'all') {
            return;
        }
        $offset = ($filter->getPage() - 1) * $filter->getLimit();
        $select->limit($filter->getLimit())
            ->offset($offset);
    }

    protected function fetchFilteredCollection(Filter $filter, Select $select): Collection
    {
        return $this->fetchPaginatedCollection(
            new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
            $this->getReadSql(),
            $select,
            $this->getMapper()
        );
    }

    protected function getBuilderForEntity(Entity $entity): BuilderInterface
    {
        return $this->builderFactory->getBuilderObjectByChannel($entity->getChannel());
    }

    /**
     * @param Entity $entity
     */
    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        $entity->setCategoryId($id);
        $entity->setNewlyInserted(true);
        $this->getBuilderForEntity($entity)->save($entity->getData());
    }

    /**
     * @param Entity $entity
     */
    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))
            ->where(array('categoryId' => $entity->getId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
        $this->getBuilderForEntity($entity)->update($entity->getData());
    }

    /**
     * @param Entity $entity
     * @return array
     */
    protected function getEntityArray($entity): array
    {
        $entityArray = $entity->toArray();
        unset($entityArray['externalData']);
        return $entityArray;
    }

    protected function getSelect(): Select
    {
        return $this->getReadSql()->select(static::DB_TABLE_NAME);
    }

    protected function getInsert(): Insert
    {
        return $this->getWriteSql()->insert(static::DB_TABLE_NAME);
    }

    protected function getUpdate(): Update
    {
        return $this->getWriteSql()->update(static::DB_TABLE_NAME);
    }

    protected function getDelete(): Delete
    {
        return $this->getWriteSql()->delete(static::DB_TABLE_NAME);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}
