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
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Db extends DbAbstract implements StorageInterface
{
    use FilterArrayValuesToOrdLikesTrait;

    const RECURSION_MSG = 'Circular dependency detected. The product you are trying to link (SKU: %s) is already used to calculate stock for another product that you are trying to link this product to (SKU: %s).';

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetch($id)
    {
        [$organisationUnitId, $sku] = explode('-', $id, 2);
        $select = $this->getSelect()->where([
            'from.organisationUnitId' => $organisationUnitId,
            'from.sku' => $sku,
            'path.order' => 0,
        ]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLink not found with id %s', $id));
        }

        $linkId = null;
        $array = null;

        foreach ($results as $data) {
            if (!is_array($array)) {
                $linkId = $data['id'];
                $array = $this->toArray($data);
            } else {
                $this->appendStockRow($array, $data);
            }
        }

        $expandedStock = [$linkId => &$array];
        $this->appendExpandedStock($expandedStock);
        return $this->mapper->fromArray($array);
    }

    /**
     * @param ProductLink $entity
     */
    protected function saveEntity($entity)
    {
        try {
            $linkId = $this->getLinkId($entity->getOrganisationUnitId(), $entity->getProductSku());
            $this->removeLinkPaths($linkId);
        } catch (NotFound $exception) {
            $linkId = $this->insertLink($entity->getOrganisationUnitId(), $entity->getProductSku());
            $entity->setNewlyInserted(true);
        }

        $paths = [];
        $edgePaths = $this->getLinkPathIdMap($linkId, 'to');

        foreach ($entity->getStockSkuMap() as $sku => $qty) {
            try {
                $childId = $this->getLinkId($entity->getOrganisationUnitId(), $sku);
            } catch (NotFound $exception) {
                $childId = $this->insertLink($entity->getOrganisationUnitId(), $sku);
            }

            $newPaths = [];
            foreach ($this->getLinkPaths($childId) as $linkPath) {
                $linkIds = [$linkId => true];
                $path[] = ['from' => $linkId, 'to' => $childId, 'quantity' => $qty, 'order' => ($order = 0)];

                foreach ($linkPath as $linkNode) {
                    if (isset($linkIds[$linkNode['from']])) {
                        throw new RecursionException(
                            sprintf(static::RECURSION_MSG, $entity->getProductSku(), $this->getLinkSku($linkNode['from']))
                        );
                    }

                    $linkIds[$linkNode['from']] = true;
                    $path[] = [
                        'from' => $linkNode['from'],
                        'to' => $linkNode['to'],
                        'quantity' => $linkNode['quantity'] * $qty,
                        'order' => ++$order
                    ];
                }
                $newPaths[] = $path;
            }

            if (empty($newPaths)) {
                $newPaths[] = [['from' => $linkId, 'to' => $childId, 'quantity' => $qty, 'order' => 0]];
            }

            foreach ($newPaths as $path) {
                $this->insertLinkPath($path);
                $paths[] = $path;
            }
        }

        foreach ($edgePaths as $pathId => $edge) {
            $edgePath = $this->getLinkPathIdMap($pathId, 'pathId');
            $this->removePath($pathId);

            foreach ($paths as $path) {
                $this->insertLinkPath(
                    array_merge($edgePath, array_map(
                        function($path) use($edge) {
                            $path['quantity'] *= $edge['quantity'];
                            $path['order'] += $edge['order'] + 1;
                            return $path;
                        },
                        $path
                    ))
                );
            }
        }

        $expandedStock = [$linkId => []];
        $this->appendExpandedStock($expandedStock);
        $entity->setExpandedSkuMap($expandedStock[$linkId]['expandedStock'] ?? []);

        return $entity;
    }

    protected function insertLink($ouId, $sku)
    {
        $insert = $this->getInsert()->values(['organisationUnitId' => $ouId, 'sku' => $sku]);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        return $this->writeSql->getAdapter()->getDriver()->getLastGeneratedValue();
    }

    protected function insertLinkPath(array $path)
    {
        $pathId = $this->getNextPathId();
        foreach ($path as $node) {
            $insert = $this->getInsert('productLinkPath')->values(['pathId' => $pathId] + $node);
            $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function getNextPathId()
    {
        $select = $this->writeSql
            ->select('productLinkPath')
            ->columns(['nextPathId' => new Expression('? + 1', ['pathId'], [Expression::TYPE_IDENTIFIER])])
            ->combine(
                $this->writeSql->select()->columns(['nextPathId' => new Expression('?', [1])]),
                Select::COMBINE_UNION,
                Select::QUANTIFIER_ALL
            );

        $select = $this->writeSql->select(['missingPaths' => $select])->order('missingPaths.nextPathId')->limit(1);
        $select->where->expression(
            'NOT EXISTS (?)',
            [
                $this->writeSql
                    ->select('productLinkPath')
                    ->columns(['pathId'])
                    ->where((new Where())->equalTo('pathId', 'missingPaths.nextPathId', Where::TYPE_IDENTIFIER, Where::TYPE_IDENTIFIER))
            ]
        );

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['nextPathId'];
        }
        return 1;
    }

    /**
     * @param ProductLink $entity
     */
    public function remove($entity)
    {
        try {
            $linkId = $this->getLinkId($entity->getOrganisationUnitId(), $entity->getProductSku());
        } catch (NotFound $exception) {
            throw new NotFound(
                sprintf('ProductLink not found with id %s', $entity->getId()),
                $exception->getCode(),
                $exception
            );
        }

        $this->removeLinkPaths($linkId);
        $this->removeLink($linkId);
    }

    protected function removeLinkPaths($linkId)
    {
        $linkIdMap = $this->getLinkPathIdMap($linkId, 'from');
        if (empty($linkIdMap)) {
            return;
        }

        $delete = $this->getDelete('productLinkPath');
        foreach ($linkIdMap as $pathId => $map) {
            $delete->where->orPredicate(
                (new Where())->equalTo('pathId', $pathId)->greaterThanOrEqualTo('order', $map['order'])
            );
        }
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();

        $duplicateLinkIdMap = $this->getLinkPathIdMap($linkId, 'to');
        if (empty($duplicateLinkIdMap)) {
            return;
        }

        $duplicates = [];
        foreach ($duplicateLinkIdMap as $pathId => $map) {
            $id = $map['from'] . '-' . $map['order'];
            if (!isset($duplicates[$id])) {
                $duplicates[$id] = [];
            } else {
                $duplicates[$id][] = $pathId;
            }
        }

        $duplicates = array_unique(array_merge(...array_values($duplicates)));
        if (!empty($duplicates)) {
            $this->removePath(...$duplicates);
        }
    }

    protected function removePath(...$pathIds)
    {
        $delete = $this->getDelete('productLinkPath')->where(['pathId' => $pathIds]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function removeLink($linkId)
    {
        if ($this->getLinkToCount($linkId, 'to') > 0) {
            return;
        }

        $delete = $this->getDelete()->where(['linkId' => $linkId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $results = $this->readSql->prepareStatementForSqlObject($this->getFilteredSelect($filter, $total))->execute();
        if ($results->count() == 0) {
            throw new NotFound('No ProductLinks found matching filter');
        }

        $map = [];
        foreach ($results as $data) {
            $id = $data['id'];
            if (!isset($map[$id])) {
                $map[$id] = $this->toArray($data);
            } else {
                $this->appendStockRow($map[$id], $data);
            }
        }
        $this->appendExpandedStock($map);

        $collection = new Collection(ProductLink::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);

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
            'stock' => [$data['stockSku'] => $data['quantity']],
            'expandedStock' => [],
        ];
    }

    protected function appendStockRow(array &$array, array $data)
    {
        $array['stock'][$data['stockSku']] = $data['quantity'];
    }

    protected function appendExpandedStock(array &$productLinkArrays)
    {
        if (empty($productLinkArrays)) {
            return;
        }

        $expanded = $this->writeSql
            ->select(['result' => 'productLinkPath'])
            ->columns(['pathId' => 'pathId', 'order' => new Expression('MAX(?)', ['result.order'], [Expression::TYPE_IDENTIFIER])])
            ->join(
                ['lookup' => 'productLinkPath'],
                'result.pathId = lookup.pathId',
                ['from']
            )
            ->where(['lookup.from' => array_keys($productLinkArrays), 'lookup.order' => 0])
            ->group(['lookup.from', 'result.pathId']);

        $select = $this->writeSql
            ->select(['path' => 'productLinkPath'])
            ->columns(['quantity'])
            ->join(
                ['link' => 'productLink'],
                'path.to = link.linkId',
                ['sku']
            )
            ->join(
                ['expanded' => $expanded],
                'path.pathId = expanded.pathId AND path.order = expanded.order',
                ['linkId' => 'from']
            );

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            if (!isset($productLinkArrays[$result['linkId']])) {
                continue;
            }

            if (!isset($productLinkArrays[$result['linkId']]['expandedStock'][$result['sku']])) {
                $productLinkArrays[$result['linkId']]['expandedStock'][$result['sku']] = 0;
            }

            $productLinkArrays[$result['linkId']]['expandedStock'][$result['sku']] += $result['quantity'];
        }
    }

    protected function getLinkId($ouId, $sku)
    {
        $select = $this->writeSql
            ->select('productLink')
            ->columns(['linkId'])
            ->where(['organisationUnitId' => $ouId, 'sku' => $sku]);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            throw new NotFound('Unable to find matching link');
        }

        return $results->current()['linkId'];
    }

    protected function getLinkSku($linkId)
    {
        $select = $this->writeSql->select('productLink')->columns(['sku'])->where(['linkId' => $linkId]);
        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['sku'];
        }
        return '';
    }

    protected function getLinkPathIdMap($id, $lookup)
    {
        $select = $this->writeSql
            ->select('productLinkPath')
            ->columns(['pathId', 'from', 'to', 'quantity', 'order'])
            ->where([$lookup => $id]);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return [];
        }

        $pathIdOrderMap = [];
        foreach ($results as $result) {
            $pathIdOrderMap[$result['pathId']] = [
                'from' => $result['from'],
                'to' => $result['to'],
                'quantity' => $result['quantity'],
                'order' => $result['order'],
            ];
        }
        return $pathIdOrderMap;
    }

    protected function getLinkToCount($linkId, $lookup)
    {
        $select = $this->writeSql
            ->select('productLinkPath')
            ->where([$lookup => $linkId]);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        return $results->count();
    }

    protected function getLinkPaths($linkId)
    {
        $select = $this->writeSql
            ->select(['result' => 'productLinkPath'])
            ->columns(['pathId', 'from', 'to', 'quantity'])
            ->join(
                ['lookup' => 'productLinkPath'],
                'result.pathId = lookup.pathId',
                []
            )
            ->where(['lookup.from' => $linkId, 'lookup.order' => 0])
            ->order(['result.pathId', 'result.order']);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return [];
        }

        $paths = [];
        foreach ($results as $result) {
            if (!isset($paths[$result['pathId']])) {
                $paths[$result['pathId']] = [];
            }
            $paths[$result['pathId']][] = $result;
        }
        return $paths;
    }

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select(['from' => 'productLink'])
            ->columns([
                'id' => 'linkId',
                'organisationUnitId' => 'organisationUnitId',
                'productSku' => 'sku',
            ])
            ->join(
                ['path' => 'productLinkPath'],
                'from.linkId = path.from',
                ['pathId' => 'pathId', 'quantity' => 'quantity']
            )
            ->join(
                ['to' => 'productLink'],
                'path.to = to.linkId',
                ['childId' => 'linkId', 'stockSku' => 'sku']
            );
    }

    protected function getFilteredSelect(Filter $filter, &$total = null)
    {
        $select = $this->getSelect()->where(['path.order' => 0]);
        $this->buildFilterQuery($select, $filter);

        $idLookup = $this->readSql
            ->select(['link' => $select])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['id']);

        $total = $this->getTotal($idLookup);
        if (($limit = $filter->getLimit()) !== 'all') {
            $idLookup
                ->limit($limit)
                ->offset(($filter->getPage() - 1) * $limit);
        }

        $results = $this->readSql->prepareStatementForSqlObject($idLookup)->execute();
        if ($results->count() == 0) {
            throw new NotFound('No ProductLinks found matching filter');
        }

        $linkIds = [];
        foreach ($results as $result) {
            $linkIds[] = $result['id'];
        }

        return $this->getSelect()->where(['path.order' => 0, 'from.linkId' => $linkIds]);
    }

    protected function getTotal(Select $idLookup): int
    {
        $select = clone $idLookup;
        $select->columns(['count' => new Expression('COUNT(? ?)', [Select::QUANTIFIER_DISTINCT, 'id'], [Expression::TYPE_LITERAL, Expression::TYPE_IDENTIFIER])]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['count'];
        }
        return 0;
    }

    protected function buildFilterQuery(Select $select, Filter $filter)
    {
        if (!empty($organisationUnitId = $filter->getOrganisationUnitId())) {
            $select->where([
                'from.organisationUnitId' => $organisationUnitId,
                'to.organisationUnitId' => $organisationUnitId,
            ]);
        }
        if (!empty($productSku = $filter->getProductSku())) {
            $select->where(['from.sku' => $productSku]);
        }
        if (!empty($stockSku = $filter->getStockSku())) {
            $this->filterArrayValuesToOrdLikes('to.sku', $stockSku, $select->where);
        }
        $this->appendOuIdProductSkuFilter($select->where, $filter->getOuIdProductSku());
    }

    protected function appendOuIdProductSkuFilter(Where $where, array $ouIdProductSkus)
    {
        if (empty($ouIdProductSkus)) {
            return;
        }

        $filter = new Where(null, WHERE::OP_OR);
        foreach ($ouIdProductSkus as $ouIdProductSku) {
            [$organisationUnitId, $productSku] = explode('-', $ouIdProductSku, 2);
            $filter->addPredicate(
                (new Where())->addPredicates(['from.organisationUnitId' => $organisationUnitId, 'from.sku' => $productSku])
            );
        }
        $where->addPredicate($filter);
    }

    protected function getInsert($table = 'productLink'): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete($table = 'productLink'): Delete
    {
        return $this->writeSql->delete($table);
    }
}