<?php
namespace CG\Order\Service\UserChange\Storage;

use CG\Order\Shared\UserChange\Collection;
use CG\Order\Shared\UserChange\Entity;
use CG\Order\Shared\UserChange\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    const DB_TABLE_NAME = 'userChange';

    public function fetchCollectionByOrderIds(array $orderIds)
    {
        try {
            $query = ['orderId' => $orderIds];
            $select = $this->getSelect()->where($query);

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $query),
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
        $delete = $this->getDelete()->where(['orderId' => $entity->getOrderId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(['orderId' => $id]),
            $this->getMapper()
        );
    }

    protected function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getId());
            $this->updateEntity($entity);
        } catch (NotFound $e) {
            $this->insertEntity($entity);
        }
        return $entity;
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();

        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))
            ->where(array('orderId' => $entity->getOrderId()));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        $array['changes'] = json_encode($array['changes']);
        return $array;
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