<?php
namespace CG\Template\Storage;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Template\Collection as TemplateCollection;
use CG\Template\Entity as TemplateEntity;
use CG\Template\StorageInterface;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    /** @var string */
    const TABLE = 'template';

    /**
     * @inheritDoc
     */
    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type)
    {
        $select = $this->getSelect();

        if ($limit !== 'all') {
            $offset = ($page - 1) * $limit;
            $select->limit($limit)
                ->offset($offset);
        }

        if (count($id)) {
            $query[static::TABLE . '.id'] = $id;
        }

        if (count($organisationUnitId)) {
            $query[static::TABLE . '.organisationUnitId'] = $organisationUnitId;
        }

        if (count($type)) {
            $query[static::TABLE . '.type'] = $type;
        }

        $select = $this->getSelect()->where($query);
        if ($limit != 'all') {
            $offset = ($page - 1) * $limit;
            $select->limit($limit)->offset($offset);
        }
        return $this->fetchPaginatedCollection(
            new TemplateCollection($this->getEntityClass(), __FUNCTION__, compact('limit', 'page', 'id', 'organisationUnitId', 'type')),
            $this->getReadSql(),
            $select,
            $this->getMapper()
        );
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

    public function fetch($id)
    {
        try {
            return parent::fetch(TemplateEntity::coerceId($id));
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

    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        $entityArray['id'] = $entity->getId(false);
        $entityArray['elements'] = json_encode($entityArray['elements']);
        $entityArray['paperPage'] = json_encode($entityArray['paperPage']);
        if ($mongoId = $entity->getMongoId()) {
            $entityArray['mongoId'] = $mongoId;
        }
        return $entityArray;
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

    protected function getEntityClass() {
        return TemplateEntity::class;
    }
}