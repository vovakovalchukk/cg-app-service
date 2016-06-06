<?php
namespace CG\Settings\SetupProgress\Storage;

use CG\Settings\SetupProgress\Collection;
use CG\Settings\SetupProgress\Entity as ProductDetail;
use CG\Settings\SetupProgress\Filter as Filter;
use CG\Settings\SetupProgress\StorageInterface;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Mapper\FromArrayInterface as ArrayMapper;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select as ZendSelect;
use Zend\Db\Sql\Sql as ZendSql;

class Db extends DbAbstract implements StorageInterface
{
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

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['setupProgressStep.organisationUnitId'] = $filter->getId();
        }
        if (!empty($filter->getStepStatus())) {
            foreach ($filter->getStepStatus() as $step => $status) {
                $query[] = new Expression('setupProgressStep.name = ? AND setupProgressStep.status = ?', [$step, $status]);
            }
        }
        return $query;
    }

    protected function fetchPaginatedCollection(CollectionInterface $collection, ZendSql $sql, ZendSelect $select, ArrayMapper $arrayMapper, $expected = false)
    {
        $select->group('setupProgressStep.organisationUnitId');
        $collection->setTotal($this->countResults($sql, $select));
        $select->reset('group');

        return $this->fetchCollection($collection, $sql, $select, $arrayMapper, $expected);
    }

    protected function fetchCollection(CollectionInterface $collection, ZendSql $sql, ZendSelect $select, ArrayMapper $arrayMapper, $expected = false)
    {
        $results = $sql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0 || ($expected !== false && $results->count() != $expected)) {
            throw new NotFound();
        }

        $entities = [];
        foreach ($results as $stepData) {
            $id = $stepData['organisationUnitId'];
            if (!isset($entities[$id])) {
                $entities[$id] = [
                    'id' => $id,
                    'steps' => [],
                ];
            }
            $entities[$id]['steps'][] = $stepData;
        }

        foreach ($entities as $entityData) {
            $collection->attach($arrayMapper->fromArray($entityData));
        }

        return $collection;
    }
    
    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(array(
                'organisationUnitId' => $id
            )),
            $this->getMapper()
        );
    }
    
    protected function fetchEntity(ZendSql $sql, ZendSelect $select, ArrayMapper $arrayMapper)
    {
        $statement = $sql->prepareStatementForSqlObject($select);

        $results = $statement->execute();
        if ($results->count() != 1) {
            throw new NotFound();
        }

        $entityData = [];
        foreach ($results as $stepData) {
            if (!isset($entityData['id'])) {
                $entityData = [
                    'id' => $stepData['organisationUnitId'],
                    'steps' => [],
                ];
            }
            $entityData['steps'][] = $stepData;
        }
        
        return $arrayMapper->fromArray($entityData);
    }

    protected function saveEntity($entity)
    {
        $delete = $this->getDelete();
        $delete->where(['organisationUnitId' => $entity->getId()]);
        $result = $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
        if ($result->getAffectedRows() == 0) {
            $entity->setNewlyInserted(true);
        }

        $insert = $this->getInsert();
        foreach ($entity->getSteps() as $step) {
            $stepData = $step->toArray();
            $stepData['organisationUnitId'] = $entity->getId();
            $insert->values($stepData);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'organisationUnitId' => $entity->getId()
        ));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select('setupProgressStep');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('setupProgressStep');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('setupProgressStep');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('setupProgressStep');
    }

    public function getEntityClass()
    {
        return ProductDetail::class;
    }
}