<?php
namespace CG\Product\ChannelDetail\Storage;

use CG\Product\ChannelDetail\Collection;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
use CG\Product\ChannelDetail\Filter;
use CG\Product\ChannelDetail\Mapper;
use CG\Product\ChannelDetail\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Update;

class Db extends DbAbstract implements StorageInterface
{
    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    /**
     * @param ProductChannelDetail $entity
     */
    protected function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getId());
            $this->updateEntity($entity);
        } catch (NotFound $exception) {
            $this->insertEntity($entity);
        }
        return $entity;
    }

    /**
     * @param ProductChannelDetail $entity
     */
    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $entity->setNewlyInserted(true);
    }

    /**
     * @param ProductChannelDetail $entity
     */
    public function remove($entity)
    {
        $delete = $this->getDelete()->where([
            'productId' => $entity->getProductId(),
            'channel' => $entity->getChannel(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $query = $this->buildFilterQuery($filter);
        $select = $this->getSelect()->where($query);

        if ($filter->getLimit() !== 'all') {
            $offset = ($filter->getPage() - 1) * $filter->getLimit();
            $select->limit($filter->getLimit())->offset($offset);
        }

        try {
            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $exception) {
            throw new StorageException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    protected function buildFilterQuery(Filter $filter): array
    {
        $query = [];
        if (!empty($id = $filter->getId())) {
            $query['productChannelDetail.id'] = $id;
        }
        if (!empty($productId = $filter->getProductId())) {
            $query['productChannelDetail.productId'] = $productId;
        }
        if (!empty($channel = $filter->getChannel())) {
            $query['productChannelDetail.channel'] = $channel;
        }
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $query['productChannelDetail.organisationUnitId'] = $organisationUnitId;
        }
        return $query;
    }

    protected function getEntityArray($entity)
    {
        $array = parent::getEntityArray($entity);
        unset($array['id'], $array['external']);
        return $array;
    }

    protected function getSelect(): Select
    {
        /** @var Select $select */
        $select = $this->getReadSql()->select('productChannelDetail');
        $select->columns([
            'id' => new Expression('CONCAT(?, ?, ?)', ['productId', '-', 'channel'], [Expression::TYPE_IDENTIFIER, Expression::TYPE_VALUE, Expression::TYPE_IDENTIFIER]),
            Select::SQL_STAR
        ]);
        return $this->getReadSql()->select(['productChannelDetail' => $select]);
    }

    protected function getInsert(): Insert
    {
        return $this->getWriteSql()->insert('productChannelDetail');
    }

    protected function getUpdate(): Update
    {
        return $this->getWriteSql()->update('productChannelDetail');
    }

    protected function getDelete(): Delete
    {
        return $this->getWriteSql()->delete('productChannelDetail');
    }

    public function getEntityClass()
    {
        return ProductChannelDetail::class;
    }
}