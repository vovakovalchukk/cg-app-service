<?php
namespace CG\Product\PickingLocation\Storage;

use CG\Product\PickingLocation\Filter;
use CG\Product\PickingLocation\Mapper;
use CG\Product\PickingLocation\Collection;
use CG\Product\PickingLocation\Entity as PickingLocation;
use CG\Product\PickingLocation\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Db implements StorageInterface
{
    /** @var Sql */
    protected $readSql;
    /** @var Mapper */
    protected $mapper;

    public function __construct(Sql $readSql, Mapper $mapper)
    {
        $this->readSql = $readSql;
        $this->mapper = $mapper;
    }

    public function fetch($id)
    {
        [$organisationUnitId, $level] = array_pad(explode('-', $id, 2), 2, null);
        if (!is_numeric($organisationUnitId) || !is_numeric($level)) {
            throw new NotFound(sprintf('No product picking locations with id %s found', $id));
        }

        $select = $this->getSelect()->where(compact('organisationUnitId', 'level'));
        $data = $this->resultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );

        if (!isset($data[$id])) {
            throw new NotFound(sprintf('No product picking locations with id %s found', $id));
        }

        return $this->mapper->fromArray($data[$id]);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $select = $this->getFilterSelect();
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $select->where->in('organisationUnitId', $organisationUnitId);
        }
        if (!empty($level = $filter->getLevel())) {
            $select->where->in('level', $level);
        }

        $total = $this->getTotal($select);
        if ($total == 0) {
            throw new NotFound('No product picking locations match requested filter');
        }

        if (($limit = $filter->getLimit()) !== 'all') {
            $select->limit($limit)->offset(($filter->getPage() - 1) * $limit);
        }

        $select = $this->getSelect()->join(
            ['id' => $select],
            'product.organisationUnitId = id.organisationUnitId AND productPickingLocation.level = id.level',
            []
        );

        $data = $this->resultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );

        if (empty($data)) {
            throw new NotFound('No product picking locations match requested filter');
        }

        $collection = new Collection(PickingLocation::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);
        foreach ($data as $entityData) {
            $collection->attach(
                $this->mapper->fromArray($entityData)
            );
        }

        return $collection;
    }

    protected function resultsToArray(ResultInterface $results): array
    {
        $data = [];
        foreach ($results as $row) {
            $id = $row['organisationUnitId'] . '-' . $row['level'];
            $data[$id] = $data[$id] ?? [
                'organisationUnitId' => $row['organisationUnitId'],
                'level' => $row['level'],
                'names' => [],
            ];
            $data[$id]['names'][] = $row['name'];
        }
        return $data;
    }

    protected function getTotal(Select $select): int
    {
        $select = $this->readSql->select(['id' => $select])->columns(['total' => new Expression('COUNT(*)')]);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        $results->rewind();
        return $results->current()['total'] ?? 0;
    }

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select('productPickingLocation')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['level', 'name'])
            ->join(
                'product',
                'productPickingLocation.productId = product.id',
                ['organisationUnitId']
            );
    }

    protected function getFilterSelect(): Select
    {
        return $this->readSql
            ->select('productPickingLocation')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['level'])
            ->join(
                'product',
                'productPickingLocation.productId = product.id',
                ['organisationUnitId']
            );
    }
}