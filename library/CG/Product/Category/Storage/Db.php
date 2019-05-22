<?php
namespace CG\Product\Category\Storage;

use CG\Product\Category\Collection;
use CG\Product\Category\Entity;
use CG\Product\Category\Filter;
use CG\Product\Category\StorageInterface;
use CG\Product\Category\VersionMap\Storage\Db as CategoryVersionMapDb;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as Predicate;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Adapter\Platform\Mysql;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'category';

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect();
            $where = new Where();

            if ($filter->getVersionMapId() !== null) {
                $this->joinOnVersionMap($select, $where, $filter);
            }

            $where->addPredicates($query, PredicateSet::OP_AND);
            $select->where($where);
            $select->group('category.id');

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
        unset($filterArray['limit'], $filterArray['page'], $filterArray['versionMapId']);

        foreach ($filterArray as $name => $filter) {
            if ($this->isFilterInPrefixBlacklist($name)) {
                break;
            }
            $newName = 'category.' . $name;
            $filterArray[$newName] = $filter;
            unset($filterArray[$name]);
        }
        return array_filter(
            $filterArray,
            function($value): bool {
                return is_array($value) ? !empty($value) : !is_null($value);
            }
        );
    }

    protected function isFilterInPrefixBlacklist(string $filterName): bool
    {
        $blacklist = [
            'limit' => true,
            'page' => true,
            'versionMapId' => true
        ];
        return isset($blacklist[$filterName]);
    }

    protected function joinOnVersionMap(Select $select, Where $where, Filter $filter)
    {
        $select->join(
            CategoryVersionMapDb::DB_CHANNEL_VERSION_MAP_TABLE_NAME,
            new Expression('category.version IS NULL OR (category.version = categoryVersionMapChannel.version
                    AND
                    (category.channel = categoryVersionMapChannel.channel)
                    AND
                    IF (category.marketplace IS NULL, TRUE, category.marketplace = categoryVersionMapChannel.marketplace)
                    AND
                    IF (category.accountId IS NULL, TRUE, category.accountId = categoryVersionMapChannel.accountId)
                )
                AND categoryVersionMapChannel.categoryVersionMapId =?', [$filter->getVersionMapId()]),
            [],
            Select::JOIN_INNER
        );
    }

    protected function getSelect(): Select
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
