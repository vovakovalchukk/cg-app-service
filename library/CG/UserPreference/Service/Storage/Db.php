<?php

namespace CG\UserPreference\Service\Storage;

use CG\Controllers\UserPreference\UserPreference;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\UserPreference\Shared\Collection as UserPreferenceCollection;
use CG\UserPreference\Shared\Entity as UserPreferenceEntity;
use CG\UserPreference\Shared\Entity;
use CG\UserPreference\Shared\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Exception\Runtime\NotFound;

class Db extends DbAbstract implements StorageInterface
{
    /** @var string */
    const TABLE = 'userPreference';

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
        if ($entity->getId(false) != null) {
            try {
                // There are instances where the entity has an ID but that ID does not exist in the database
                $dbEntity = $this->fetch($entity->getId(false));
                // Ensure that the entity's mongo ID is maintained as this is lost as we save
                $entity->setMongoId($dbEntity->getMongoId());
                $this->updateEntity($entity);
            } catch (NotFound $ignored) {
                $this->insertEntity($entity);
            }
            return $entity;
        }

        if (null == $entity->getMongoId()) {
            $this->insertEntity($entity);
            return $entity;
        }

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

        return $entity;
    }

    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        $entityArray['id'] = $entity->getId(false);
        $entityArray['preference'] = json_encode($entityArray['preference']);
        if ($mongoId = $entity->getMongoId()) {
            $entityArray['mongoId'] = $mongoId;
        }
        return $entityArray;
    }


    public function fetch($id)
    {
        try {
            return parent::fetch(UserPreferenceEntity::coerceId($id));
        } catch (NotFound|\InvalidArgumentException $exception) {
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
        return $this->writeSql->update(self::TABLE);
    }

    protected function getSelect()
    {
        return $this->readSql->select(self::TABLE);
    }

    protected function getDelete()
    {
        return $this->writeSql->delete(self::TABLE);
    }
    
    protected function getInsert()
    {
        return $this->writeSql->insert(self::TABLE);
    }

    protected function getEntityClass()
    {
        return UserPreferenceEntity::class;
    }
}