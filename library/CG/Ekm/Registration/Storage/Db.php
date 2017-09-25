<?php
namespace CG\Ekm\Registration\Storage;

use CG\Ekm\Registration\Collection;
use CG\Ekm\Registration\Entity as Registration;
use CG\Ekm\Registration\Filter;
use CG\Ekm\Registration\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    const DB_TABLE_NAME = 'ekmRegistration';

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
        if (!empty($filter->getEkmUsername())) {
            $query[static::DB_TABLE_NAME . '.ekmUsername'] = $filter->getEkmUsername();
        }
        if (!empty($filter->getToken())) {
            $query[static::DB_TABLE_NAME . '.token'] = $filter->getToken();
        };
        if (!empty($filter->getRootOrganisationUnitId())) {
            $query[static::DB_TABLE_NAME . '.rootOrganisationUnitId'] = $filter->getRootOrganisationUnitId();
        };

        return $query;
    }

    public function fetchCollectionByPagination($limit, $page, array $id)
    {
        try {
            $query = [];
            if(count($id)) {
                $query[static::DB_TABLE_NAME . '.id'] = $id;
            }

            $select = $this->getSelect()
                ->where($query);

            if($limit != 'all') {
                $offset = ($page - 1) * $limit;
                $select->limit($limit)
                    ->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__,
                    compact('limit', 'page', 'id')),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function saveEntity($entity)
    {
        if($this->entityNotStored($entity)) {
            $this->insertEntity($entity);
        } else {
            $this->updateEntity($entity);
        }
        return $entity;
    }

    protected function entityNotStored($entity)
    {
        $select = $this->getSelect()->where(['id' => $entity->getId()]);
        $statement = $this->getReadSql()->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        return $results->count() === 0;
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
        return Registration::class;
    }
}