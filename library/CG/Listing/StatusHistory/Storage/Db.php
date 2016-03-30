<?php
namespace CG\Listing\StatusHistory\Storage;

use CG\Listing\StatusHistory\Collection;
use CG\Listing\StatusHistory\Entity;
use CG\Listing\StatusHistory\Filter;
use CG\Listing\StatusHistory\Mapper;
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

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        return $this->fetchPaginatedCollection(
            new Collection(Entity::class, __FUNCTION__, $filter->toArray()),
            $this->getReadSql(),
            $this->buildFilterdSelect($filter),
            $this->getMapper()
        );
    }

    protected function buildFilterdSelect(Filter $filter)
    {
        $select = $this->getSelect();

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

            $select = $this->getSelect()
                ->join(
                    ['max' => $max],
                    sprintf('%s.id = max.id', static::TABLE_NAME),
                    []
                );
        }

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
    protected function getSelect()
    {
        return $this->getReadSql()->select(static::TABLE_NAME);
    }

    /**
     * @return Insert
     */
    protected function getInsert()
    {
        return $this->getReadSql()->insert(static::TABLE_NAME);
    }

    /**
     * @return Update
     */
    protected function getUpdate()
    {
        return $this->getReadSql()->update(static::TABLE_NAME);
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->getReadSql()->delete(static::TABLE_NAME);
    }
} 
