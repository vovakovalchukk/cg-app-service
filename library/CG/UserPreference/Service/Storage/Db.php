<?php

namespace CG\UserPreference\Service\Storage;

use CG\Stdlib\Storage\Db\DbAbstract;
use CG\UserPreference\Shared\Collection as UserPreferenceCollection;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;
use CG\UserPreference\Shared\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Exception\Runtime\NotFound;

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

            if ($limit !== 'all') {
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

    protected function saveEntity($entity)
    {
        if ($entity->getId(false) == null) {
            if (null != $entity->getMongoId()) {
                try {
                    $dbEntity = $this->fetchEntity(
                        $this->getReadSql(),
                        $this->getSelect()->where(array(
                            'mongoId' => $entity->getMongoId()
                        )),
                        $this->getMapper()
                    );

                    $entity->setId($dbEntity->getId());
                    $this->updateEntity($entity);
                } catch (NotFound $ignored) {
                    $this->insertEntity($entity);
                }
            } else {
                $this->insertEntity($entity);
            }
        } else {
            $this->updateEntity($entity);
        }
        return $entity;
    }

    public function fetch($id)
    {
        try {
            return parent::fetch($id);
        } catch (NotFound $exception) {
            return $this->fetchEntity(
                $this->getReadSql(),
                $this->getSelect()->where(array(
                    'mongoId' => $id
                )),
                $this->getMapper()
            );
        }
    }

    protected function getUpdate()
    {
        return $this->readSql->update(self::TABLE);
    }

    protected function getSelect()
    {
        return $this->readSql->select(self::TABLE);
    }

    protected function getDelete()
    {
        return $this->readSql->delete(self::TABLE);
    }

    protected function getInsert()
    {
        return $this->readSql->insert(self::TABLE);
    }

    protected function getEntityClass()
    {
        return UserPreferenceEntity::class;
    }
}