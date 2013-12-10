<?php
namespace CG\App\Service\Event\Storage;

use CG\App\Service\Event\StorageInterface;
use CG\Stdlib\Storage\Db\Zend\Sql as SqlStorage;
use Zend\Db\Sql\Sql;
use CG\App\Service\Event\Mapper;
use CG\App\Service\Event\Collection;
use CG\App\Service\Event\Entity;
use CG\App\Service\Storage\Db as ServiceDb;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Db\Sql\Exception\ExceptionInterface;
use CG\Stdlib\Exception\Storage as StorageException;

class Db implements StorageInterface, EventManagerAwareInterface
{
    use SqlStorage;

    protected $readSql;
    protected $fastReadSql;
    protected $writeSql;
    protected $mapper;
    protected $eventManager;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper, EventManagerInterface $eventManager)
    {
        $this->setReadSql($readSql)
             ->setFastReadSql($fastReadSql)
             ->setWriteSql($writeSql)
             ->setMapper($mapper)
             ->setEventManager($eventManager);
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

    public function setFastReadSql(Sql $fastReadSql)
    {
        $this->fastReadSql = $fastReadSql;
        return $this;
    }

    public function getFastReadSql()
    {
        return $this->fastReadSql;
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

    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this)
        ));
        $this->eventManager = $eventManager;
        return $this;
    }

    public function getEventManager()
    {
        return $this->eventManager;
    }

    protected  function getSelect()
    {
        return $this->getReadSql()
            ->select("serviceEvent")
            ->columns(array(
                'id',
                'serviceId',
                'type',
                'instances',
                'endpoint'
            ));
    }

    protected function getJoinSelect()
    {
        return $this->getReadSql()->select(array("se" => "serviceEvent"))
                                  ->join(array("s" => "service"), "se.serviceId = s.id");
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert("serviceEvent");
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update("serviceEvent");
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete("serviceEvent");
    }

    public function setMapper(Mapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
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

    public function fetchCollectionByServiceId($limit, $page, $serviceId)
    {
        try {
            $offset = ($page - 1) * $limit;
            $query = array("serviceEvent.serviceId" => $serviceId);
            $select = $this->getSelect()->where($query)->limit($limit)->offset($offset);
            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page', 'serviceId')),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function fetchCollectionByServiceIds(array $serviceIds)
    {
        try {
            $query = array("serviceEvent.serviceId" => $serviceIds);
            $select = $this->getSelect()->where($query);
            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, array(compact('serviceIds'))),
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
        $insert = $this->getInsert()->values($entity->toArray());
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();

        $entity->setId(
            $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue()
        );
        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set(
            $entity->toArray()
        )->where(array(
            'id' => $entity->getId()
        ));

        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    protected function updateRelatedEntities($entity)
    {
        $select = $this->getJoinSelect()->join(array('s_related' => 'service'), 's.type = s_related.type AND s.id <> s_related.id')
            ->join(array('se_related' => 'serviceEvent'), 's_related.id = se_related.serviceId AND se.type = se_related.type',array('relatedId' => 'id'))
            ->where(array('se.id' => $entity->getId()));

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

    public function getEntityClass()
    {
        return Entity::class;
    }
}
