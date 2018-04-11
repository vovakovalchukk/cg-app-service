<?php
namespace CG\Product\ChannelDetail\Storage;

use CG\Product\ChannelDetail\Collection;
use CG\Product\ChannelDetail\Entity as ProductChannelDetail;
use CG\Product\ChannelDetail\Filter;
use CG\Product\ChannelDetail\Mapper;
use CG\Product\ChannelDetail\Storage\External\Factory as ExternalStorageFactory;
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
    /** @var ExternalStorageFactory */
    protected $externalStorageFactory;

    public function __construct(
        Sql $readSql,
        Sql $fastReadSql,
        Sql $writeSql,
        Mapper $mapper,
        ExternalStorageFactory $externalStorageFactory
    ) {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
        $this->externalStorageFactory = $externalStorageFactory;
    }

    public function fetch($id)
    {
        /** @var ProductChannelDetail $entity */
        $entity = parent::fetch($id);
        $this->fetchExternal($entity);
        return $entity;
    }

    protected function fetchExternal(ProductChannelDetail $entity)
    {
        $entity->setExternal(
            $this
                ->externalStorageFactory
                ->getStorageForChannel($entity->getChannel())
                ->fetch($entity->getProductId())
        );
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
        $this->saveExternal($entity);
        return $entity;
    }

    protected function saveExternal(ProductChannelDetail $entity)
    {
        $this
            ->externalStorageFactory
            ->getStorageForChannel($entity->getChannel())
            ->save($entity->getProductId(), $entity->getExternal());
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
    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))->where([
            'productId' => $entity->getProductId(),
            'channel' => $entity->getChannel(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    /**
     * @param ProductChannelDetail $entity
     */
    public function remove($entity)
    {
        $this->removeExternal($entity);
        $delete = $this->getDelete()->where([
            'productId' => $entity->getProductId(),
            'channel' => $entity->getChannel(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function removeExternal(ProductChannelDetail $entity)
    {
        $this
            ->externalStorageFactory
            ->getStorageForChannel($entity->getChannel())
            ->remove($entity->getProductId());
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
            /** @var Collection $collection */
            $collection = $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $exception) {
            throw new StorageException($exception->getMessage(), $exception->getCode(), $exception);
        }

        foreach ($collection->getArrayOf('channel') as $channel) {
            $this->fetchMultipleExternal($channel, $collection->getBy('channel', $channel));
        }

        return $collection;
    }

    protected function fetchMultipleExternal(string $channel, Collection $collection)
    {
        $externals = $this
            ->externalStorageFactory
            ->getStorageForChannel($channel)
            ->fetchMultiple($collection->getArrayOf('productId'));

        foreach ($externals as $productId => $external) {
            /** @var ProductChannelDetail $entity */
            foreach ($collection->getBy('productId', $productId) as $entity) {
                $entity->setExternal($external);
            }
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