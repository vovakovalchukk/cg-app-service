<?php
namespace CG\Product\CategoryDetail\Storage;

use CG\Product\CategoryDetail\Collection;
use CG\Product\CategoryDetail\Entity as ProductCategoryDetail;
use CG\Product\CategoryDetail\Filter;
use CG\Product\CategoryDetail\Mapper;
use CG\Product\CategoryDetail\Storage\External\Factory as ExternalStorageFactory;
use CG\Product\CategoryDetail\StorageInterface;
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
        /** @var ProductCategoryDetail $entity */
        $entity = parent::fetch($id);
        $this->fetchExternal($entity);
        return $entity;
    }

    protected function fetchExternal(ProductCategoryDetail $entity)
    {
        $entity->setExternal(
            $this
                ->externalStorageFactory
                ->getStorageForChannel($entity->getChannel())
                ->fetch($entity->getProductId(), $entity->getCategoryId())
        );
    }

    /**
     * @param ProductCategoryDetail $entity
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

    protected function saveExternal(ProductCategoryDetail $entity)
    {
        $this
            ->externalStorageFactory
            ->getStorageForChannel($entity->getChannel())
            ->save($entity->getProductId(), $entity->getCategoryId(), $entity->getExternal());
    }

    /**
     * @param ProductCategoryDetail $entity
     */
    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $entity->setNewlyInserted(true);
    }

    /**
     * @param ProductCategoryDetail $entity
     */
    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))->where([
            'productId' => $entity->getProductId(),
            'categoryId' => $entity->getCategoryId(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    /**
     * @param ProductCategoryDetail $entity
     */
    public function remove($entity)
    {
        $this->removeExternal($entity);
        $delete = $this->getDelete()->where([
            'productId' => $entity->getProductId(),
            'categoryId' => $entity->getCategoryId(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function removeExternal(ProductCategoryDetail $entity)
    {
        $this
            ->externalStorageFactory
            ->getStorageForChannel($entity->getChannel())
            ->remove($entity->getProductId(), $entity->getCategoryId());
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
        $ids = [];
        /** @var ProductCategoryDetail $entity */
        foreach ($collection as $entity) {
            $ids[] = [$entity->getProductId(), $entity->getCategoryId()];
        }

        $externals = $this->externalStorageFactory->getStorageForChannel($channel)->fetchMultiple($ids);
        foreach ($externals as $productId => $categoryExternal) {
            foreach ($categoryExternal as $categoryId => $external) {
                /** @var ProductCategoryDetail $entity */
                $entity = $collection->getById($productId . '-' . $categoryId);
                $entity->setExternal($external);
            }
        }
    }

    protected function buildFilterQuery(Filter $filter): array
    {
        $query = [];
        if (!empty($id = $filter->getId())) {
            $query['productCategoryDetail.id'] = $id;
        }
        if (!empty($productId = $filter->getProductId())) {
            $query['productCategoryDetail.productId'] = $productId;
        }
        if (!empty($categoryId = $filter->getCategoryId())) {
            $query['productCategoryDetail.categoryId'] = $categoryId;
        }
        if (!empty($channel = $filter->getChannel())) {
            $query['productCategoryDetail.channel'] = $channel;
        }
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $query['productCategoryDetail.organisationUnitId'] = $organisationUnitId;
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
        $select = $this->getReadSql()->select('productCategoryDetail');
        $select->columns([
            'id' => new Expression('CONCAT(?, ?, ?)', ['productId', '-', 'categoryId'], [Expression::TYPE_IDENTIFIER, Expression::TYPE_VALUE, Expression::TYPE_IDENTIFIER]),
            Select::SQL_STAR
        ]);
        return $this->getReadSql()->select(['productCategoryDetail' => $select]);
    }

    protected function getInsert(): Insert
    {
        return $this->getWriteSql()->insert('productCategoryDetail');
    }

    protected function getUpdate(): Update
    {
        return $this->getWriteSql()->update('productCategoryDetail');
    }

    protected function getDelete(): Delete
    {
        return $this->getWriteSql()->delete('productCategoryDetail');
    }

    public function getEntityClass()
    {
        return ProductCategoryDetail::class;
    }
}