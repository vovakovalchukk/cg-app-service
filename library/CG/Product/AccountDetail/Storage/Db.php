<?php
namespace CG\Product\AccountDetail\Storage;

use CG\Product\AccountDetail\Collection;
use CG\Product\AccountDetail\Entity as ProductAccountDetail;
use CG\Product\AccountDetail\Filter;
use CG\Product\AccountDetail\Mapper;
use CG\Product\AccountDetail\StorageInterface;
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
     * @param ProductAccountDetail $entity
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
     * @param ProductAccountDetail $entity
     */
    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $entity->setNewlyInserted(true);
    }

    /**
     * @param ProductAccountDetail $entity
     */
    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))->where([
            'productId' => $entity->getProductId(),
            'accountId' => $entity->getAccountId(),
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    /**
     * @param ProductAccountDetail $entity
     */
    public function remove($entity)
    {
        $delete = $this->getDelete()->where([
            'productId' => $entity->getProductId(),
            'accountId' => $entity->getAccountId(),
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
            $query['id'] = $id;
        }
        if (!empty($productId = $filter->getProductId())) {
            $query['productId'] = $productId;
        }
        if (!empty($accountId = $filter->getAccountId())) {
            $query['accountId'] = $accountId;
        }
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $query['organisationUnitId'] = $organisationUnitId;
        }
        return $query;
    }

    protected function getEntityArray($entity)
    {
        $array = parent::getEntityArray($entity);
        unset($array['id']);
        $array['externalData'] = json_encode($array['externalData'] ?? null);
        return $array;
    }

    protected function getSelect(): Select
    {
        /** @var Select $select */
        $select = $this->getReadSql()->select('productAccountDetail');
        $select->columns([
            'id' => new Expression('CONCAT(?, ?, ?)', ['productId', '-', 'accountId'], [Expression::TYPE_IDENTIFIER, Expression::TYPE_VALUE, Expression::TYPE_IDENTIFIER]),
            Select::SQL_STAR
        ]);
        return $this->getReadSql()->select(['productAccountDetail' => $select]);
    }

    protected function getInsert(): Insert
    {
        return $this->getWriteSql()->insert('productAccountDetail');
    }

    protected function getUpdate(): Update
    {
        return $this->getWriteSql()->update('productAccountDetail');
    }

    protected function getDelete(): Delete
    {
        return $this->getWriteSql()->delete('productAccountDetail');
    }

    public function getEntityClass()
    {
        return ProductAccountDetail::class;
    }
}