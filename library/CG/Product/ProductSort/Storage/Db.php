<?php
namespace CG\Product\ProductSort\Storage;

use CG\Product\ProductSort\Collection;
use CG\Product\ProductSort\Entity as ProductSort;
use CG\Product\ProductSort\Filter as Filter;
use CG\Product\ProductSort\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Expression;

class Db extends DbAbstract implements StorageInterface
{
    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $total = $this->fetchEntityCount($query);
            if ($total == 0) {
                throw new NotFound('No matching ProductSorts found matching requested filters');
            }
            $select = $this->getSelect()->quantifier(Select::QUANTIFIER_DISTINCT)->columns(['id'])->where($query);

            if (!empty($filter->getLimit()) && $filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            $ids = array_column(
                iterator_to_array($this->getReadSql()->prepareStatementForSqlObject($select)->execute()),
                'id'
            );

            if (empty($ids)) {
                throw new NotFound('No matching ProductSorts found matching requested filters');
            }

            $productSorts = $this->fetchCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $this->getSelect()->where(['id' => $ids]),
                $this->getMapper()
            );
            $productSorts->setTotal($total);
            return $productSorts;

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function fetchEntityCount($query)
    {
        $select = $this->getReadSql()
            ->select('productSort')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['count' => new Expression('COUNT(id)')])
            ->where($query);

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        return $results->current()['count'];
    }

    protected function buildFilterQuery(Filter $filter)
    {
        if (empty($filter->getId())) {
            $query = $this->getDefaultFilterForUser($filter);
        } else { // generic filter
            $query = [
                'productSort.id' => $filter->getId(),
            ];
        }
        return $query;
    }

    protected function getSelect(): Select
    {
        /** @var Select $select */
        $select = $this->getReadSql()->select('productSort');
        return $select;
    }

    /**
     * @param Filter $filter
     * @return Predicate\PredicateSet
     */
    protected function getDefaultFilterForUser(Filter $filter): Predicate\PredicateSet
    {
        return new Predicate\PredicateSet([
            new Predicate\Operator('productSort.organisationUnitId', Predicate\Operator::OPERATOR_EQUAL_TO, $filter->getOrganisationUnitId()),
            new Predicate\PredicateSet([
                    new Predicate\Operator('productSort.userId', Predicate\Operator::OPERATOR_EQUAL_TO, $filter->getUserId()),
                    new Predicate\IsNull('productSort.userId'),
                ],
                Predicate\PredicateSet::COMBINED_BY_OR
            ),
        ]);
    }

    protected function getInsert($table = 'productSort'): Insert
    {
        return $this->getWriteSql()->insert($table);
    }

    protected function getUpdate($table = 'productSort'): Update
    {
        return $this->getWriteSql()->update($table);
    }

    protected function getDelete($table = 'productSort'): Delete
    {
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return ProductSort::class;
    }
}
