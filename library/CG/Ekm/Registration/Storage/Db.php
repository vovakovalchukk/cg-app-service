<?php
namespace CG\Settings\PackageRules\Storage;

use CG\Ekm\Registration\Request\Collection;
use CG\Ekm\Registration\Request\Entity as PackageRules;
use CG\Ekm\Registration\Request\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByPagination($limit, $page, array $id)
    {
        try {
            $query = [];
            if(count($id)) {
                $query['request.id'] = $id;
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
        return $this->getReadSql()->select('request');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('request');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('request');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('request');
    }

    public function getEntityClass()
    {
        return PackageRules::class;
    }
}