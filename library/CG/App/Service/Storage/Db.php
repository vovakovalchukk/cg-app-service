<?php
namespace CG\App\Service\Storage;

use CG\App\Service\StorageInterface;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use Zend\Db\Sql\Sql;
use CG\App\Service\Mapper;
use CG\App\Service\Collection;
use CG\App\Service\Entity;
use Zend\Db\Sql\Exception\ExceptionInterface;
use CG\Stdlib\Exception\Storage as StorageException;

class Db implements StorageInterface
{
    use SqlStorage;

    protected $readSql;
    protected $fastReadSql;
    protected $writeSql;
    protected $mapper;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        $this->setReadSql($readSql)
             ->setFastReadSql($fastReadSql)
             ->setWriteSql($writeSql)
             ->setMapper($mapper);
    }

    protected function getSelect()
    {
        return $this->getReadSql()
            ->select("service")
            ->columns(array(
                'id',
                'type',
                'endpoint'
            ));
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert("service");
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update("service");
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete("service");
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'id' => $id
            )),
            $this->getMapper()
        );
    }

    public function fetchCollectionWithPagination($limit, $page)
    {
        try {
            $offset = ($page - 1) * $limit;
            $select = $this->getSelect()->where(array())->limit($limit)->offset($offset);

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page')),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function save($entity)
    {
        if ($entity->getId() !== null) {
            return $this->updateEntity($entity);
        }

        $this->insertEntity($entity);
    }

    protected function getSelect()
    {
        return $this->getReadSql()
            ->select(static::TABLE)
            ->columns(array(
                'id',
                'type',
                'endpoint'
            ));
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($entity->toArray());
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();

        $entity->setId(
            $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue()
        );
        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($entity->toArray())->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    public function setFastReadSql(Sql $fastReadSql)
    {
        $this->fastReadSql = $fastReadSql;
        return $this;
    }

    public function getFastReadSql()
    {
        return $this->fastReadSql;
    }

    public function setReadSql(Sql $readSql)
    {
        $this->readSql = $readSql;
        return $this;
    }

    public function getReadSql()
    {
        return $this->readSql;
    }

    public function setWriteSql(Sql $writeSql)
    {
        $this->writeSql = $writeSql;
        return $this;
    }

    public function getWriteSql()
    {
        return $this->writeSql;
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}
