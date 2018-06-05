<?php
namespace CG\Product\Category\VersionMap\Storage;

use CategoryVersionMap;
use CG\Product\Category\VersionMap\Collection;
use CG\Product\Category\VersionMap\Entity;
use CG\Product\Category\VersionMap\Filter;
use CG\Product\Category\VersionMap\Mapper;
use CG\Product\Category\VersionMap\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'categoryVersionMap';
    const DB_CHANNEL_VERSION_MAP_TABLE_NAME = 'categoryVersionMapChannel';

    /** @var Sql $readSql */
    protected $readSql;
    /** @var Mapper $mapper */
    protected $mapper;

    public function __construct(Sql $readSql, Mapper $mapper)
    {
        $this->readSql = $readSql;
        $this->mapper = $mapper;
    }

    public function fetch($id)
    {
        $select =$this->getSelect()
            ->where(['categoryVersionMapId' => $id]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound();
        }

        $data = $this->getDataFromResult($results);

        return $this->mapper->fromArray(reset($data));
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);
            $total = $this->getTotalVersionMaps($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
            $results = $this->getDataFromResult($results);

            $collection = new Collection(CategoryVersionMap::class, __FUNCTION__, $filter->toArray());
            $collection->setTotal($total);

            foreach($results as $result) {
                $collection->attach($this->mapper->fromArray($result));
            }

            return $collection;
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getTotalVersionMaps($query = null): int
    {
        $select = $this->getReadSql()->select();
        $select->from(self::DB_TABLE_NAME)
            ->columns([
            'count' => new Expression(
                'COUNT(? ?)',
                [Select::QUANTIFIER_DISTINCT, 'id'],
                [Expression::TYPE_LITERAL, Expression::TYPE_IDENTIFIER]
            )
        ]);
        if ($query !== null) {
            if (isset($query['categoryVersionMapId'])) {
                $query['id'] = $query['categoryVersionMapId'];
                unset($query['categoryVersionMapId']);
            }
            $select->where($query);
        }

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['count'];
        }
        return 0;
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $filterArray = $filter->toArray();
        unset($filterArray['limit'], $filterArray['page']);
        if (isset($filterArray['id'])) {
            $filterArray['categoryVersionMapId'] = $filterArray['id'];
            unset($filterArray['id']);
        }
        return array_filter(
            $filterArray,
            function($value): bool {
                return is_array($value) ? !empty($value) : !is_null($value);
            }
        );
    }

    protected function insertEntity($entity)
    {

        $versionMapId = $this->insertVersionMap($entity)->getId();

        foreach ($entity->getVersionMap() as $channelVersionMap) {
            $values = array_merge(['id' => null, 'categoryVersionMapId' => $versionMapId], $channelVersionMap->toArray());
            $insert = $this->getWriteSql()
                ->insert(static::DB_CHANNEL_VERSION_MAP_TABLE_NAME)
                ->values($values);

            $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function insertVersionMap($entity): Entity
    {
        $insert = $this->getInsert()->values(['id' => null]);
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();

        $id = $this->getWriteSql()->getAdapter()->getDriver()->getLastGeneratedValue();

        $entity->setId($id);
        $entity->setNewlyInserted(true);
        return $entity;
    }

    protected function getSelect()
    {
        return $this->getReadSql()
            ->select(static::DB_TABLE_NAME)
            ->columns(['categoryVersionMapId' =>'id'])
            ->join(
                ['channel' => static::DB_CHANNEL_VERSION_MAP_TABLE_NAME],
                'channel.categoryVersionMapId = categoryVersionMap.id',
                ['channel', 'marketplace', 'accountId', 'version']
            );
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert(static::DB_TABLE_NAME);
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update(static::DB_TABLE_NAME);
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete(static::DB_TABLE_NAME);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }

    protected function getDataFromResult($results): array
    {
        $data = [];
        foreach ($results as $result) {
            $id = $result['categoryVersionMapId'];
            $data[$id]['id'] = $result['categoryVersionMapId'];
            $data[$id]['versionMap'][] = $this->getVersionMapFromData($result);
        }
        return $data;
    }
    protected function getVersionMapFromData($data): array
    {
        return [
            'channel' => $data['channel'],
            'marketplace' => $data['marketplace'],
            'accountId' => $data['accountId'],
            'version' => $data['version']
        ];
    }

    public function getLatestId(): int
    {
        $select = $this->getReadSql()->select();
        $select->from(self::DB_TABLE_NAME)
            ->order(['id DESC']);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if (!$results->count() > 0) {
            throw new NotFound();
        }

        foreach ($results as $result) {
            return $result['id'];
        }
        return 0;
    }
}
