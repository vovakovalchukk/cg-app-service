<?php
namespace Application\Service\Storage;

use Application\Service\Storage;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use Zend\Db\Sql\Sql;
use Application\Service\Mapper;
use Zend\Db\Sql\Select;
use Application\Service\Collection;
use Application\Service\Entity;

class Db implements Storage
{
    use SqlStorage;

    const TABLE = 'service';
    const TABLE_ALIAS = 's';

    protected $readSql;
    protected $fastReadSql;
    protected $writeSql;
    protected $mapper;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        $this->setReadSql($readSql);
        $this->setFastReadSql($fastReadSql);
        $this->setWriteSql($writeSql);
        $this->setMapper($mapper);
    }

    public function setReadSql(Sql $readSql)
    {
        $this->readSql = $readSql;
    }

    public function getReadSql()
    {
        return $this->readSql;
    }

    public function setFastReadSql(Sql $fastReadSql)
    {
        $this->fastReadSql = $fastReadSql;
    }

    public function getFastReadSql()
    {
        return $this->fastReadSql;
    }

    public function setWriteSql($writeSql)
    {
        $this->writeSql = $writeSql;
    }

    public function getWriteSql()
    {
        return $this->writeSql;
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

    protected function getInsert()
    {
        return $this->getWriteSql()->insert(static::TABLE);
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update(static::TABLE);
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete(static::TABLE);
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

    public function fetchByIds(array $ids)
    {
        return $this->fetchCollection(
            new Collection(Entity::getClass(), __FUNCTION__, compact('ids')),
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'id' => $ids
            )),
            $this->getMapper(),
            count($ids)
        );
    }

    public function fetchAll()
    {
        return $this->fetchCollection(
            new Collection(Entity::getClass(), __FUNCTION__),
            $this->getReadSql(),
            $this->getSelect(),
            $this->getMapper()
        );
    }

    public function save($entity)
    {
        if ($entity->getId() !== null) {
            return $this->updateEntity($entity);
        }

        $this->insertEntity($entity);
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getMapper()->toArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();

        $entity->setId(
            $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue()
        );
        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set(
            $this->getMapper()->toArray($entity)
        )->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }
}
