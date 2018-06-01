<?php
namespace CG\Product\Category\VersionMap\Storage;

use CG\Product\Category\VersionMap\Collection;
use CG\Product\Category\VersionMap\Entity;
use CG\Product\Category\VersionMap\Filter;
use CG\Product\Category\VersionMap\Mapper;
use CG\Product\Category\VersionMap\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;
use CG\Product\Category\VersionMap\ChannelVersionMap;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'categoryVersionMap';

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
        $select = $this->readSql
            ->select(self::DB_TABLE_NAME)
            ->columns(['categoryVersionMapId' =>'id'])
            ->join(
                ['channel' => 'categoryVersionMapChannel'],
                'channel.categoryVersionMapId = categoryVersionMap.id',
                ['channel', 'marketplace', 'accountId', 'channelVersion' => 'version']
            )
            ->where(['categoryVersionMapId' => $id])
            ->order('categoryVersionMapId DESC');

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        $channelVersionMaps = [];
        foreach ($results as $result) {
            $id = $result['categoryVersionMapId'];
            $channelVersionMaps[] = ChannelVersionMap::fromArray($result)->toArray();
        }

        return $this->mapper->fromArray(
            [
                'id' => $id,
                'versionMap' => $channelVersionMaps
            ]
        );
    }

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
        $filterArray = $filter->toArray();
        unset($filterArray['limit'], $filterArray['page']);
        return array_filter(
            $filterArray,
            function($value): bool {
                return is_array($value) ? !empty($value) : !is_null($value);
            }
        );
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select(static::DB_TABLE_NAME);
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
}
