<?php
namespace CG\Listing\StatusHistory\Storage;

use CG\Listing\StatusHistory\Collection;
use CG\Listing\StatusHistory\Entity;
use CG\Listing\StatusHistory\Filter;
use CG\Listing\StatusHistory\Storage\Db\Mapper;
use CG\Listing\StatusHistory\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;

/**
 * @method Sql getFastReadSql
 * @method Sql getReadSql
 * @method Sql getWriteSql
 * @method Mapper getMapper
 */
class Db extends DbAbstract implements StorageInterface
{
    const TABLE_NAME = 'listingStatusHistory';
    const TABLE_NAME_CODE = 'listingStatusHistoryCode';
    const SEPERATOR = ',';

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchPaginatedCollection(
            new Collection(Entity::class, __FUNCTION__, $filter->toArray()),
            $this->getReadSql(),
            $this->buildFilteredSelect($filter),
            $this->getMapper()
        );
    }

    protected function buildFilteredSelect(Filter $filter)
    {
        $select = $this->getSelect(false);

        if (count($id = $filter->getId())) {
            $select->where(['id' => $id]);
        }
        if (count($listingId = $filter->getListingId())) {
            $select->where(['listingId' => $listingId]);
        }
        if (count($status = $filter->getStatus())) {
            $select->where(['status' => $status]);
        }

        if ($filter->getLatest()) {
            $max = $select
                ->columns(['id' => new Expression('MAX(?)', ['id'], [Expression::TYPE_IDENTIFIER])])
                ->group('listingId');

            $select = $this->getSelect(false)
                ->join(
                    ['max' => $max],
                    sprintf('%s.id = max.id', static::TABLE_NAME),
                    []
                );
        }

        $this->joinToCodeTable($select);
        if (($limit = $filter->getLimit()) != 'all') {
            $select
                ->limit($limit)
                ->offset(($filter->getPage() - 1) * $limit);
        }
        return $select->order(sprintf('%s.id DESC', static::TABLE_NAME));
    }

    /**
     * @return Select
     */
    protected function getSelect($joinToCodeTable = true)
    {
        $select = $this->getReadSql()->select(static::TABLE_NAME);
        if ($joinToCodeTable) {
            $this->joinToCodeTable($select);
        }
        return $select;
    }

    /**
     * @return self
     */
    protected function joinToCodeTable(Select $select)
    {
        $select
            ->join(
                static::TABLE_NAME_CODE,
                sprintf('%s.id = %s.listingStatusHistoryId', static::TABLE_NAME, static::TABLE_NAME_CODE),
                ['code' => new Expression('GROUP_CONCAT(? SEPARATOR \'?\')', ['code', static::SEPERATOR], [Expression::TYPE_IDENTIFIER, Expression::TYPE_LITERAL])],
                Select::JOIN_LEFT
            )
            ->group(sprintf('%s.id', static::TABLE_NAME));
        return $this;
    }

    /**
     * @return array
     */
    protected function getEntityArray($entity)
    {
        $entityArray = parent::getEntityArray($entity);
        unset($entityArray['code']);
        return $entityArray;
    }

    /**
     * @param Entity $entity
     */
    protected function insertEntity($entity)
    {
        parent::insertEntity($entity);
        $this->storeCodesForEntity($entity);
    }

    /**
     * @param Entity $entity
     */
    protected function updateEntity($entity)
    {
        parent::updateEntity($entity);
        $this->storeCodesForEntity($entity);
    }

    /**
     * @return self
     */
    protected function storeCodesForEntity(Entity $entity)
    {
        $delete = $this
            ->getDelete(static::TABLE_NAME_CODE)
            ->where(['listingStatusHistoryId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();

        foreach ($entity->removeDuplicateCodes() as $code) {
            $insert = $this
                ->getInsert(static::TABLE_NAME_CODE)
                ->values(['listingStatusHistoryId' => $entity->getId(), 'code' => $code]);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }

        return $this;
    }

    /**
     * @return Insert
     */
    protected function getInsert($table = self::TABLE_NAME)
    {
        return $this->getReadSql()->insert($table);
    }

    /**
     * @return Update
     */
    protected function getUpdate($table = self::TABLE_NAME)
    {
        return $this->getWriteSql()->update($table);
    }

    /**
     * @return Delete
     */
    protected function getDelete($table = self::TABLE_NAME)
    {
        return $this->getWriteSql()->delete($table);
    }
} 
