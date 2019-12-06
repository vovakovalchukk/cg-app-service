<?php
namespace CG\Order\Service\Label\Storage\MetaData;

use CG\Order\Service\Label\Storage\MetaDataInterface;
use CG\Order\Shared\Label\Collection;
use CG\Order\Shared\Label\Entity;
use CG\Order\Shared\Label\Filter;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Exception\Storage as StorageException;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Predicate\Operator;

class Db extends DbAbstract implements MetaDataInterface
{
    protected const LOG_CODE = 'LabelMetaDataDb';

    public function fetch($id)
    {
        $entity = parent::fetch($id);
        $this->fetchParcelsAndAddToEntity($entity);
        return $entity;
    }

    protected function fetchParcelsAndAddToEntity(Entity $entity)
    {
        $select = $this->getReadSql()->select('orderLabelParcel');
        $select->where(['orderLabelId' => $entity->getId()]);
        $parcels = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        $entity->setParcels(iterator_to_array($parcels, false));
        return $this;
    }

    public function fetchCollectionByFilter(Filter $filter): Collection
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            $collection = $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
            $this->fetchParcelsAndAddToCollection($collection);
            return $collection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];
        if ($filter->getId()) {
            $query["id"] = $filter->getId();
        }
        if ($filter->getOrganisationUnitId()) {
            $query["organisationUnitId"] = $filter->getOrganisationUnitId();
        }
        if ($filter->getOrderId()) {
            $query["orderId"] = $filter->getOrderId();
        }
        if ($filter->getStatus()) {
            $query["status"] = $filter->getStatus();
        }
        if ($filter->getShippingAccountId()) {
            $query["shippingAccountId"] = $filter->getShippingAccountId();
        }
        if ($filter->getCreatedFrom()) {
            $query[] = new Operator('orderLabel.created', Operator::OP_GTE, $filter->getCreatedFrom());
        }
        if ($filter->getCreatedTo()) {
            $query[] = new Operator('orderLabel.created', Operator::OP_LTE, $filter->getCreatedTo());
        }
        if ($filter->getShippingServiceCode()) {
            $query["shippingServiceCode"] = $filter->getShippingServiceCode();
        }
        if ($filter->getMongoId()) {
            $query["mongoId"] = $filter->getMongoId();
        }
        return $query;
    }

    protected function fetchParcelsAndAddToCollection(Collection $collection)
    {
        $select = $this->getReadSql()->select('orderLabelParcel');
        $select->where(['orderLabelId' => $collection->getIds()]);
        $parcels = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        $parcelsByLabel = [];
        foreach ($parcels as $parcel) {
            $orderLabelId = $parcel['orderLabelId'];
            if (!isset($parcelsByLabel[$orderLabelId])) {
                $parcelsByLabel[$orderLabelId] = [];
            }
            $parcelsByLabel[$orderLabelId][] = $parcel;
        }
        foreach ($parcelsByLabel as $orderLabelId => $parcels) {
            $entity = $collection->getById($orderLabelId);
            $entity->setParcels($parcels);
        }
        return $this;
    }

    protected function insertEntity($entity)
    {
        parent::insertEntity($entity);
        $this->updateParcels($entity);
    }

    protected function insertParcels($entity)
    {
        if (empty($entity->getParcels())) {
            return;
        }
        $insert = $this->getWriteSql()->insert('orderLabelParcel');
        foreach ($entity->getParcels() as $parcel) {
            $parcel['orderLabelId'] = $entity->getId();
            $insert->values($parcel);
            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    public function remove($entity)
    {
        $this->logDebug('About to remove orderLabel entity data in %s', [__METHOD__], static::LOG_CODE);
        parent::remove($entity);
        $this->removeParcels($entity);
        $this->logDebug('Completed removing orderLabel entity data in %s', [__METHOD__], static::LOG_CODE);
    }

    protected function removeParcels($entity)
    {
        $this->logDebug('About to remove orderLabelParcel entity data in %s', [__METHOD__], static::LOG_CODE);
        $delete = $this->getWriteSql()->delete('orderLabelParcel')->where([
            'orderLabelId' => $entity->getId()
        ]);
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
        $this->logDebug('Completed removing orderLabelParcel entity data in %s', [__METHOD__], static::LOG_CODE);
    }

    protected function updateEntity($entity)
    {
        parent::updateEntity($entity);
        $this->updateParcels($entity);
    }

    protected function updateParcels($entity)
    {
        $this->removeParcels($entity);
        $this->insertParcels($entity);
    }

    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        unset($array['label'], $array['image'], $array['parcels']);
        return $array;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select('orderLabel');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('orderLabel');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('orderLabel');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('orderLabel');
    }

    protected function getEntityClass()
    {
        return $this->mapper->getEntityClass();
    }
}
