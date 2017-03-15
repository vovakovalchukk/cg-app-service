<?php
namespace CG\Order\Service\ShipmentMetadata\Storage;

use CG\Order\Shared\ShipmentMetadata\Collection;
use CG\Order\Shared\ShipmentMetadata\Entity;
use CG\Order\Shared\ShipmentMetadata\Filter;
use CG\Order\Shared\ShipmentMetadata\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if (!empty($filter->getId())) {
            $query['shipmentMetadataCountry.organisationUnitId'] = $filter->getId();
        }

        return $query;
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(['organisationUnitId' => $id]),
            $this->getMapper()
        );
    }

    protected function saveEntity($entity)
    {
        $delete = $this->getDelete();
        $delete->where(['organisationUnitId' => $entity->getId()]);
        $result = $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
        if ($result->getAffectedRows() == 0) {
            $entity->setNewlyInserted(true);
        }

        $insert = $this->getInsert();
        foreach ($entity->getCountryCodes() as $countryCode) {
            $insert->values(['organisationUnitId' => $entity->getId(), 'countryCode' => $countryCode]);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(['organisationUnitId' => $entity->getId()]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect()
    {
        $select = $this->getReadSql()->select('shipmentMetadataCountry');
        $select->columns([
            'id' => 'organisationUnitId',
            'countryCodes' => new Expression('GROUP_CONCAT(shipmentMetadataCountry.countryCode SEPARATOR ",")')
            ])
            ->group('shipmentMetadataCountry.organisationUnitId');
        return $select;
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('shipmentMetadataCountry');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('shipmentMetadataCountry');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('shipmentMetadataCountry');
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}