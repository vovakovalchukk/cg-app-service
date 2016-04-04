<?php
namespace CG\Amazon\Carrier\Storage;

use CG\Amazon\Carrier\Collection;
use CG\Amazon\Carrier\Entity;
use CG\Amazon\Carrier\Filter;
use CG\Amazon\Carrier\Mapper;
use CG\Amazon\Carrier\StorageInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Zend\Stdlib\Db\Sql\Replace;
use CG\Zend\Stdlib\Db\Sql\Sql;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Select;

/**
 * @method Sql getFastReadSql
 * @method Sql getReadSql
 * @method Sql getWriteSql
 * @method Mapper getMapper
 */
class Db extends DbAbstract implements StorageInterface
{
    const TABLE = 'courier';

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    /**
     * @param Entity $entity
     */
    protected function saveEntity($entity)
    {
        $replace = $this->getReplace()->values($entity->toArray());
        $result = $this->getWriteSql()->prepareStatementForSqlObject($replace)->execute();
        if ($result->getAffectedRows() == 1) {
            $entity->setNewlyInserted(true);
        }
        return $entity;
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $select = $this->getSelect()->where($this->filterToParams($filter));
        if (($limit = $filter->getLimit()) !== 'all') {
            $offset = ($limit * ($filter->getPage() - 1));
            $select->limit($limit)->offset($offset);
        }

        return $this->fetchPaginatedCollection(
            new Collection(Entity::class, __FUNCTION__, $filter->toArray()),
            $this->getReadSql(),
            $select,
            $this->getMapper()
        );
    }

    protected function filterToParams(Filter $filter)
    {
        $params = [];
        if (count($filter->getId())) {
            $params['id'] = $filter->getId();
        }
        if (count($filter->getCarrier())) {
            $params['carrier'] = $filter->getCarrier();
        }
        if (count($filter->getService())) {
            $params['service'] = $filter->getService();
        }
        if (count($filter->getCurrencyCode())) {
            $params['currencyCode'] = $filter->getCurrencyCode();
        }
        if (count($filter->getDeliveryExperience())) {
            $params['deliveryExperience'] = $filter->getDeliveryExperience();
        }
        if (count($filter->getCarrierWillPickUp())) {
            $params['carrierWillPickUp'] = $filter->getCarrierWillPickUp();
        }
        return $params;
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->getReadSql()->select(static::TABLE);
    }

    /**
     * @return Replace
     */
    protected function getReplace()
    {
        return $this->getWriteSql()->replace(static::TABLE);
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->getWriteSql()->delete(static::TABLE);
    }
} 