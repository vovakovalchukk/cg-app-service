<?php
namespace CG\PurchaseOrder\Storage;

use CG\PurchaseOrder\Collection;
use CG\PurchaseOrder\Entity;
use CG\PurchaseOrder\Filter;
use CG\PurchaseOrder\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'purchaseOrder';

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
            $query['id'] = $filter->getId();
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $query['organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getStatus())) {
            $query['status'] = $filter->getStatus();
        }
        if (!empty($filter->getExternalId())) {
            $query['externalId'] = $filter->getExternalId();
        }
        if (!empty($filter->getCreatedFrom())) {
            $query['created >= ?'] = $filter->getCreatedFrom();
        }
        if (!empty($filter->getCreatedTo())) {
            $query['created <= ?'] = $filter->getCreatedTo();
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
