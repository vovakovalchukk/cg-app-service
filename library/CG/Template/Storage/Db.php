<?php
namespace CG\Template\Storage;

use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Template\StorageInterface;
use CG\Template\Entity as TemplateEntity;
use CG\Template\Collection as TemplateCollection;
use CG\Stdlib\Exception\Storage as StorageException;

class Db extends DbAbstract implements StorageInterface
{
    /** @var string */
    const TABLE = 'template';

    /**
     * @inheritDoc
     */
    public function fetchCollectionByPagination($limit, $page, array $id, array $organisationUnitId, array $type)
    {
        try {
            $select = $this->getSelect();

            if($limit !== 'all') {
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
        return TemplateEntity::class;
    }
}