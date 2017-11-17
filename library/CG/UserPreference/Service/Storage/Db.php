<?php

namespace CG\UserPreference\Service\Storage;

use CG\Stdlib\Storage\Db\DbAbstract;
use CG\UserPreference\Shared\Collection as UserPreferenceCollection;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;
use CG\UserPreference\Shared\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;

class Db extends DbAbstract implements StorageInterface
{
    /** @var string */
    const TABLE = 'userPreferences';

    /**
     * @inheritDoc
     */
    public function fetchCollectionByPagination($limit, $page)
    {
        try {
            $select = $this->getSelect();

            if($limit !== 'all') {
                $offset = ($page - 1) * $limit;
                $select->limit($limit)
                    ->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new UserPreferenceCollection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page')),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (\Exception $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return \Zend\Db\Sql\Update
     */
    protected function getUpdate()
    {
        return $this->readSql->update(self::TABLE);
    }

    /**
     * @return \Zend\Db\Sql\Select
     */
    protected function getSelect()
    {
        return $this->readSql->select(self::TABLE);
    }

    /**
     * @return \Zend\Db\Sql\Delete
     */
    protected function getDelete()
    {
        return $this->readSql->delete(self::TABLE);
    }

    /**
     * @return \Zend\Db\Sql\Insert
     */
    protected function getInsert()
    {
        return $this->readSql->insert(self::TABLE);
    }

    protected function getEntityClass() {
        return UserPreferenceEntity::class;
    }
}