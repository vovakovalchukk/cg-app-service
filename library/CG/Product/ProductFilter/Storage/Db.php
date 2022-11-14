<?php
namespace CG\Product\ProductFilter\Storage;

use CG\Product\ProductFilter\Collection;
use CG\Product\ProductFilter\Entity as ProductFilter;
use CG\Product\ProductFilter\Filter as Filter;
use CG\Product\ProductFilter\StorageInterface;
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
                throw new NotFound('No matching ProductFilters found matching requested filters');
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
                throw new NotFound('No matching ProductFilters found matching requested filters');
            }

            $productFilters = $this->fetchCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $this->getSelect()->where(['id' => $ids]),
                $this->getMapper()
            );
            $productFilters->setTotal($total);
            return $productFilters;

        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function fetchEntityCount($query)
    {
        $select = $this->getReadSql()
            ->select('productFilter')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['count' => new Expression('COUNT(id)')])
            ->where($query);

        $results = $this->getReadSql()->prepareStatementForSqlObject($select)->execute();
        return $results->current()['count'];
    }

    protected function buildFilterQuery(Filter $filter)
    {
        if (!empty($filter->getOrganisationUnitId()) && empty($filter->getId())) {
            if (empty($filter->getUserId())) {
                $query = $this->getDefaultFilterForOrg($filter);
            } else {
                $query = $this->getDefaultFilterForUser($filter);
            }
        } else { // generic filter
            $query = [];
            if (!empty($filter->getId())) {
                $query['productFilter.id'] = $filter->getId();
            }
            if (!empty($filter->getOrganisationUnitId())) {
                $query['productFilter.organisationUnitId'] = $filter->getOrganisationUnitId();
            }
            if (!empty($filter->getUserId())) {
                $query['productFilter.userId'] = $filter->getUserId();
            }
            if (!empty($filter->isDefaultFilter())) {
                $query['productFilter.defaultFilter'] = $filter->isDefaultFilter();
            }
        }
        return $query;
    }

    protected function getSelect(): Select
    {
        /** @var Select $select */
        $select = $this->getReadSql()->select('productFilter');
        return $select;
    }

    /**
     * @param Filter $filter
     * @return Predicate\PredicateSet
     */
    public function getDefaultFilterForOrg(Filter $filter): Predicate\PredicateSet
    {
        return new Predicate\PredicateSet([
            new Predicate\Operator('productFilter.organisationUnitId', Predicate\Operator::OPERATOR_EQUAL_TO, $filter->getOrganisationUnitId()),
            new Predicate\IsNull('productFilter.userId'),
            new Predicate\Operator('productFilter.defaultFilter', Predicate\Operator::OPERATOR_EQUAL_TO, true),
        ]);
    }

    /**
     * @param Filter $filter
     * @return Predicate\PredicateSet
     */
    public function getDefaultFilterForUser(Filter $filter): Predicate\PredicateSet
    {
        return new Predicate\PredicateSet([
            new Predicate\Operator('productFilter.organisationUnitId', Predicate\Operator::OPERATOR_EQUAL_TO, $filter->getOrganisationUnitId()),
            new Predicate\PredicateSet([
                    new Predicate\Operator('productFilter.userId', Predicate\Operator::OPERATOR_EQUAL_TO, $filter->getUserId()),
                    new Predicate\IsNull('productFilter.userId'),
                ],
                Predicate\PredicateSet::COMBINED_BY_OR
            ),
            new Predicate\Operator('productFilter.defaultFilter', Predicate\Operator::OPERATOR_EQUAL_TO, true),
        ]);
    }

    protected function getInsert($table = 'productFilter'): Insert
    {
        return $this->getWriteSql()->insert($table);
    }

    protected function getUpdate($table = 'productFilter'): Update
    {
        return $this->getWriteSql()->update($table);
    }

    protected function getDelete($table = 'productFilter'): Delete
    {
        return $this->getWriteSql()->delete($table);
    }

    public function getEntityClass()
    {
        return ProductFilter::class;
    }
}
