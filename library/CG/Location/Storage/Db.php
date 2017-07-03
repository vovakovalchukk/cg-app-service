<?php
namespace CG\Location\Storage;

use CG\Location\Collection;
use CG\Location\Entity;
use CG\Location\Filter;
use CG\Location\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    const DB_TABLE_NAME = 'location';

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
            $query[static::DB_TABLE_NAME . '.id'] = $filter->getId();
        }
        if (!empty($filter->getType())) {
            $query[static::DB_TABLE_NAME . '.type'] = $filter->getType();
        }
        if (!empty($filter->getIncludeStockOnAllChannels())) {
            $query[static::DB_TABLE_NAME . '.includeStockOnAllChannels'] = $filter->getIncludeStockOnAllChannels();
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $query[static::DB_TABLE_NAME . '.organisationUnitId'] = $filter->getOrganisationUnitId();
        }

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

    public function getEntityClass()
    {
        return Entity::class;
    }
}