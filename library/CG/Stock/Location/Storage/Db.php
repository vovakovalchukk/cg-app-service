<?php
namespace CG\Stock\Location\Storage;

use CG\Stdlib\CollectionInterface;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\PreconditionFailed;
use CG\Stdlib\Exception\Runtime\Storage\Deadlock;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stock\Collection as Stock;
use CG\Stock\Location\Collection as LocationCollection;
use CG\Stock\Location\Entity as LocationEntity;
use CG\Stock\Location\Filter;
use CG\Stock\Location\Mapper;
use CG\Stock\Location\StorageInterface;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetchCollectionForStock(Stock $stock)
    {
        return $this->fetchCollectionByStockIds($stock->getIds());
    }

    public function fetchCollectionByStockIds(array $stockIds)
    {
        return $this->fetchCollectionByFilter(
            new Filter('all', 1, $stockIds)
        );
    }

    public function fetchCollectionByPaginationAndFilters($limit, $page, array $stockId, array $locationId)
    {
        return $this->fetchCollectionByFilter(
            new Filter($limit, $page, $stockId, $locationId)
        );
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $select = $this->getSelect()->where($this->getQueryForFilter($filter));
            $this->appendOuIdSkuFilter($select->where, $filter->getOuIdSku());

            $limit = $filter->getLimit();
            if ($limit != 'all') {
                $offset = ($filter->getPage() - 1) * $limit;
                $select->limit($limit)->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new LocationCollection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getQueryForFilter(Filter $filter)
    {
        $query = [];
        if (!empty($stockId = $filter->getStockId())) {
            $query['stockLocation.stockId'] = $stockId;
        }
        if (!empty($locationId = $filter->getLocationId())) {
            $query['stockLocation.locationId'] = $locationId;
        }
        return $query;
    }

    protected function appendOuIdSkuFilter(Where $where, array $ouIdSkus)
    {
        if (empty($ouIdSkus)) {
            return;
        }

        $filter = new Where(null, Where::OP_OR);
        foreach ($ouIdSkus as $ouIdSku) {
            [$organisationUnitId, $sku] = explode('-', $ouIdSku, 2);
            $filter->addPredicate(
                (new Where())->equalTo('organisationUnitId', $organisationUnitId)->equalTo('sku', $sku)
            );
        }
        $where->addPredicate($filter);
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'locationId' => $entity->getLocationId(),
            'stockId' => $entity->getStockId(),
        ));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    public function fetch($id)
    {
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where(LocationEntity::getStockAndLocationFromId($id)),
            $this->getMapper()
        );
    }

    public function save($entity, array $adjustmentIds = [])
    {
        $attempts = 5;
        try {
            $this->startTransactionAndHandleDeadlock([$this, 'saveEntityWithAdjustments'], [$entity, $adjustmentIds], $attempts);
        } catch (Deadlock $e) {
            $this->logError('Deadlock handling failed, attempted %s times to save entity of type %s', [$attempts, get_class($entity)], 'MySQL Deadlock');
            throw $e;
        }
        return $entity;
    }

    protected function saveEntityWithAdjustments($entity, array $adjustmentIds)
    {
        try {
            $this->fetch($entity->getId());
            $this->updateEntityWithAdjustments($entity, $adjustmentIds);
        } catch (NotFound $ex) {
            $this->insertEntityWithAdjustments($entity, $adjustmentIds);
        }
        return $entity;
    }

    protected function insertEntityWithAdjustments($entity, array $adjustmentIds)
    {
        $insert = $this->getInsert()->values($this->toDbArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $this->insertAdjustmentIds($adjustmentIds);

        $entity->setNewlyInserted(true);
    }

    protected function updateEntityWithAdjustments($entity, array $adjustmentIds)
    {
        $update = $this->getUpdate()->set($this->toDbArray($entity))
            ->where(array(
                'locationId' => $entity->getLocationId(),
                'stockId' => $entity->getStockId(),
            ));
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
        $this->insertAdjustmentIds($adjustmentIds);
    }

    protected function insertAdjustmentIds(array $adjustmentIds)
    {
        if (empty($adjustmentIds)) {
            return;
        }

        foreach ($adjustmentIds as $adjustmentId) {
            try {
                $insert = $this->getWriteSql()->insert('stockTransaction');
                $insert->values([
                        'id' => $adjustmentId,
                        'appliedDate' => (new StdlibDateTime())->stdFormat(),
                    ]);
                $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
            } catch (Conflict $conflict) {
                throw new PreconditionFailed(
                    sprintf('Adjustment Id %s has previously been applied - preventing stock location update', $adjustmentId),
                    0,
                    $conflict
                );
            }
        }
    }

    public function saveCollection(CollectionInterface $collection)
    {
        foreach ($collection as $entity) {
            $this->save($entity);
        }
    }

    protected function toDbArray($entity)
    {
        $data = $entity->toArray();
        unset($data['id']);
        return $data;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select('stockLocation');
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert('stockLocation');
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update('stockLocation');
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete('stockLocation');
    }

    public function getEntityClass()
    {
        return LocationEntity::class;
    }
}
