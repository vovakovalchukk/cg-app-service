<?php
namespace Application\Service\Event\Storage;

use Application\Service\Event\Storage;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use Zend\Db\Sql\Sql;
use Application\Service\Event\Mapper;
use Zend\Db\Sql\Select;
use CG\Stdlib\Exception\Runtime\NotFound;
use Application\Service\Event\Collection;
use Application\Service\Event\Entity;
use Application\Service\Storage\Db as ServiceDb;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class Db implements Storage, EventManagerAwareInterface
{
    use SqlStorage;

    const TABLE = 'service_event';
    const TABLE_ALIAS = 'se';

    protected $readSql;
    protected $fastReadSql;
    protected $writeSql;
    protected $mapper;
    protected $eventManager;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper, EventManagerInterface $eventManager)
    {
        $this->setReadSql($readSql);
        $this->setFastReadSql($fastReadSql);
        $this->setWriteSql($writeSql);
        $this->setMapper($mapper);
        $this->setEventManager($eventManager);
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

    public function setWriteSql(Sql $writeSql)
    {
        $this->writeSql = $writeSql;
    }

    public function getWriteSql()
    {
        return $this->writeSql;
    }
    
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this)
        ));
        $this->eventManager = $eventManager;
    }
    
    public function getEventManager()
    {
        return $this->eventManager;
    }

    protected  function getSelect()
    {
        return $this->getReadSql()
            ->select(static::TABLE)
            ->columns(array(
                'id',
                'serviceId' => 'service_id',
                'type',
                'instances',
                'endpoint'
            ));
    }

    protected function getJoinSelect()
    {
        return $this->getReadSql()->select(
                array(
                    static::TABLE_ALIAS => static::TABLE
                )
            )->join(
                array(
                    ServiceDb::TABLE_ALIAS => ServiceDb::TABLE
                ),
                static::TABLE_ALIAS . '.service_id = ' . ServiceDb::TABLE_ALIAS . '.id'
            );
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

    public function fetchCollectionByServiceIdAndType($serviceId, $type)
    {
        $entity = $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'service_id' => $serviceId,
                'type' => $type
            )),
            $this->getMapper()
        );
        $collection = new Collection(get_class($entity), __FUNCTION__, compact('serviceId', 'type'));
        $collection->attach($entity);
        return $collection;
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

    public function fetchCollectionByServiceId($serviceId)
    {
        return $this->fetchCollection(
            new Collection(Entity::getClass(), __FUNCTION__, compact('serviceId')),
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'service_id' => $serviceId
            )),
            $this->getMapper()
        );
    }

    public function fetchCollectionByType($type)
    {
        return $this->fetchCollection(
            new Collection(Entity::getClass(), __FUNCTION__, compact('type')),
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'type' => $type
            )),
            $this->getMapper()
        );
    }

    public function save($entity)
    {
        $this->getWriteSql()->getAdapter()->getDriver()->getConnection()->beginTransaction();

        if ($entity->getId() !== null) {
            $this->updateEntity($entity);
        } else {
            $this->insertEntity($entity);
        }
        $this->updateRelatedEntities($entity);

        $this->getWriteSql()->getAdapter()->getDriver()->getConnection()->commit();
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

    protected function updateRelatedEntities($entity)
    {
        $select = $this->getJoinSelect()
            ->join(
                array(
                    ServiceDb::TABLE_ALIAS . '_related' => ServiceDb::TABLE
                ),
                ServiceDb::TABLE_ALIAS . '.type = ' . ServiceDb::TABLE_ALIAS . '_related.type'
                    . ' AND ' . ServiceDb::TABLE_ALIAS . '.id <> ' . ServiceDb::TABLE_ALIAS . '_related.id'
            )->join(
                array(
                    static::TABLE_ALIAS . '_related' => static::TABLE
                ),
                ServiceDb::TABLE_ALIAS . '_related.id = ' . static::TABLE_ALIAS . '_related.service_id'
                    . ' AND ' . static::TABLE_ALIAS . '.type = ' . static::TABLE_ALIAS . '_related.type',
                array(
                    'relatedId' => 'id'
                )
            )->where(array(
                static::TABLE_ALIAS . '.id' => $entity->getId()
            ));

        $results = $this->getWriteSql()->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return;
        }

        $ids = array();
        foreach ($results as $result) {
            $ids[] = $result['relatedId'];
        }

        $update = $this->getUpdate()->set(array(
            'instances' => $entity->getInstances()
        ))->where(array(
            'id' => $ids
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
        
        $entities = $this->fetchByIds($ids);
        $this->getEventManager()->trigger('entitiesChanged', $this, ['entities' => $entities]);
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }
}
