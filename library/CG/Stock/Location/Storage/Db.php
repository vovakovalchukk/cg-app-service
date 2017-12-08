<?php
namespace CG\Stock\Location\Storage;

use CG\Http\Exception\Exception4xx\UnprocessableEntity;
use CG\Stdlib\CollectionInterface;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\PreconditionFailed;
use CG\Stdlib\Exception\Runtime\Storage\Deadlock;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stock\Location\Collection as LocationCollection;
use CG\Stock\Location\Entity as LocationEntity;
use CG\Stock\Location\Filter;
use CG\Stock\Location\Mapper;
use CG\Stock\Location\StorageInterface;
use Zend\Db\Adapter\Exception\RuntimeException as ZendDbException;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;

class Db extends DbAbstract implements StorageInterface
{
    protected const ERROR_REGEX_FOREIGN_KEY = '|a foreign key constraint fails \(.*?FOREIGN KEY \(`?(?<key>.*?)`?\).*?\)|i';
    protected const ERROR_MESSAGE_FOREIGN_KEY = 'Can not save stock location as %1$s is not valid, please confirm using correct %1$s';

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
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
            /** @var Select $select */
            $select = $this->getSelect()->where($this->getQueryForFilter($filter));
            $this->appendOuIdSkuFilter($select, $filter->getOuIdSku());

            if (($limit = $filter->getLimit()) != 'all') {
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

    protected function appendOuIdSkuFilter(Select $select, array $ouIdSkus)
    {
        if (empty($ouIdSkus)) {
            return;
        }

        $select->join(
            'stock',
            'stockLocation.stockId = stock.id',
            []
        );

        $filter = new Where(null, Where::OP_OR);
        foreach ($ouIdSkus as $ouIdSku) {
            [$organisationUnitId, $sku] = array_pad(explode('-', $ouIdSku, 2), 2, '');
            $filter->addPredicate(
                (new Where())
                    ->equalTo('stock.organisationUnitId', $organisationUnitId)
                    ->like('stock.sku', escapeLikeValue($sku))
            );
        }

        $select->where->addPredicate($filter);
    }

    public function remove($stockLocation)
    {
        $delete = $this->getDelete()->where(array(
            'locationId' => $stockLocation->getLocationId(),
            'stockId' => $stockLocation->getStockId(),
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

    public function save($stockLocation, array $adjustmentIds = [])
    {
        $attempts = 5;
        try {
            $this->startTransactionAndHandleDeadlock([$this, 'saveEntityWithAdjustments'], [$stockLocation, $adjustmentIds], $attempts);
        } catch (Deadlock $e) {
            $this->logError('Deadlock handling failed, attempted %s times to save entity of type %s', [$attempts, get_class($stockLocation)], 'MySQL Deadlock');
            throw $e;
        }
        return $stockLocation;
    }

    protected function saveEntityWithAdjustments($stockLocation, array $adjustmentIds)
    {
        try {
            try {
                $this->fetch($stockLocation->getId());
                $this->updateEntityWithAdjustments($stockLocation, $adjustmentIds);
            } catch (NotFound $ex) {
                $this->insertEntityWithAdjustments($stockLocation, $adjustmentIds);
            }
        } catch (ZendDbException $exception) {
            throw $this->parseZendDbException($exception);
        }
        return $stockLocation;
    }

    protected function insertEntityWithAdjustments($stockLocation, array $adjustmentIds)
    {
        $insert = $this->getInsert()->values($this->toDbArray($stockLocation));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $this->insertAdjustmentIds($adjustmentIds);

        $stockLocation->setNewlyInserted(true);
    }

    protected function updateEntityWithAdjustments($stockLocation, array $adjustmentIds)
    {
        $update = $this->getUpdate()->set($this->toDbArray($stockLocation))
            ->where(array(
                'locationId' => $stockLocation->getLocationId(),
                'stockId' => $stockLocation->getStockId(),
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

    protected function parseZendDbException(ZendDbException $exception) : \Exception
    {
        if (preg_match(static::ERROR_REGEX_FOREIGN_KEY, $exception->getMessage(), $match)) {
            return new UnprocessableEntity(
                sprintf(static::ERROR_MESSAGE_FOREIGN_KEY, $match['key']),
                UnprocessableEntity::HTTP_CODE,
                $exception
            );
        }
        return $exception;
    }

    public function saveCollection(CollectionInterface $collection)
    {
        foreach ($collection as $stockLocation) {
            $this->save($stockLocation);
        }
    }

    protected function toDbArray($stockLocation)
    {
        $data = $stockLocation->toArray();
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
