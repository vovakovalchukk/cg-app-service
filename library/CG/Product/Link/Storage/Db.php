<?php
namespace CG\Product\Link\Storage;

use CG\Product\Link\Collection;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter;
use CG\Product\Link\Mapper;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    use FilterArrayValuesToOrdLikesTrait;

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetch($id)
    {
        list($organisationUnitId, $sku) = explode('-', $id, 2);
        $select = $this->getSelect()->where([
            'organisationUnitId' => $organisationUnitId,
            'productSku' => $sku,
        ]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLink not found with id %s', $id));
        }

        $array = false;
        foreach ($results as $data) {
            if (!$array) {
                $array = $this->toArray($data);
            } else {
                $this->appendStockRow($array, $data);
            }
        }

        return $this->mapper->fromArray($array);
    }

    /**
     * @param ProductLink $entity
     */
    protected function saveEntity($entity)
    {
        $rowData = [
            'organisationUnitId' => $entity->getOrganisationUnitId(),
            'productSku' => $entity->getProductSku(),
        ];

        try {
            $this->remove($entity);
        } catch (NotFound $exception) {
            $entity->setNewlyInserted(true);
        }

        foreach ($entity->getStockSkuMap() as $sku => $qty) {
            $insert = $this->getInsert()->values(array_merge($rowData, ['stockSku' => $sku, 'quantity' => $qty]));
            $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        }

        return $entity;
    }

    /**
     * @param ProductLink $entity
     */
    public function remove($entity)
    {
        $delete = $this->getDelete()->where([
            'organisationUnitId' => $entity->getOrganisationUnitId(),
            'productSku' => $entity->getProductSku(),
        ]);

        $result = $this->writeSql->prepareStatementForSqlObject($delete)->execute();
        if ($result->count() == 0) {
            throw new NotFound(sprintf('ProductLink not found with id %s', $entity->getId()));
        }
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $results = $this->readSql->prepareStatementForSqlObject($this->getFilteredSelect($filter))->execute();
        if ($results->count() == 0) {
            throw new NotFound('No ProductLinks found matching filter');
        }

        $map = [];
        foreach ($results as $data) {
            $id = $data['organisationUnitId'] . '-' . $data['productSku'];
            if (!isset($map[$id])) {
                $map[$id] = $this->toArray($data);
            } else {
                $this->appendStockRow($map[$id], $data);
            }
        }

        $collection = new Collection(ProductLink::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal(count($map));

        foreach ($map as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }

        return $collection;
    }

    protected function toArray(array $data)
    {
        return [
            'organisationUnitId' => $data['organisationUnitId'],
            'sku' => $data['productSku'],
            'stock' => [[
                'sku' => $data['stockSku'],
                'quantity' => $data['quantity'],
            ]]
        ];
    }

    protected function appendStockRow(array &$array, array $data)
    {
        $array['stock'][] = [
            'sku' => $data['stockSku'],
            'quantity' => $data['quantity'],
        ];
    }

    /**
     * @return Select
     */
    protected function getSelect()
    {
        return $this->readSql->select('productLink');
    }

    protected function getFilteredSelect(Filter $filter)
    {
        $select = $this->getSelect()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['organisationUnitId', 'productSku']);

        $this->buildFilterQuery($select, $filter);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            throw new NotFound('No ProductLinks found matching filter');
        }

        $where = new Where(null, Where::OP_OR);
        foreach ($results as $result) {
            $where->addPredicate(
                (new Where())->addPredicates(['organisationUnitId' => $result['organisationUnitId'], 'productSku' => $result['productSku']])
            );
        }
        return $this->getSelect()->where($where);
    }

    protected function buildFilterQuery(Select $select, Filter $filter)
    {
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $select->where(['organisationUnitId' => $organisationUnitId]);
        }
        if (!empty($productSku = $filter->getProductSku())) {
            $select->where(['productSku' => $productSku]);
        }
        if (!empty($stockSku = $filter->getStockSku())) {
            $this->filterArrayValuesToOrdLikes('stockSku', $stockSku, $select->where);
        }
        if (($limit = $filter->getLimit()) !== 'all') {
            $select
                ->limit($limit)
                ->offset(($filter->getPage() - 1) * $limit);
        }
    }

    /**
     * @return Insert
     */
    protected function getInsert()
    {
        return $this->writeSql->insert('productLink');
    }

    /**
     * @return Delete
     */
    protected function getDelete()
    {
        return $this->writeSql->delete('productLink');
    }
}