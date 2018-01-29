<?php
namespace CG\Template\Storage;

use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Template\StorageInterface;
use CG\Template\Entity as TemplateEntity;
use CG\Template\Collection as TemplateCollection;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Exception\Runtime\NotFound;

class Db extends DbAbstract implements StorageInterface
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
            try {
                $dbEntity = $this->fetch($entity->getId(false));
                $this->updateEntity($entity);
            } catch (NotFound $ignored) {
                $this->insertEntity($entity);
            }
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

    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        $entityArray['elements'] = json_encode($entityArray['elements']);
        $entityArray['paperPage'] = json_encode($entityArray['paperPage']);
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