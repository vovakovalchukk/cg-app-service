<?php
namespace CG\Product\Link\Storage;

use CG\Product\Link\Collection;
use CG\Product\Link\Entity as ProductLink;
use CG\Product\Link\Filter;
use CG\Product\Link\Mapper;
use CG\Product\Link\StorageInterface;
use CG\Stdlib\Exception\Runtime\Conflict;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\Storage\Deadlock;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;

class Db extends DbAbstract implements StorageInterface
{
    use FilterArrayValuesToOrdLikesTrait;
    use DbLinkIdTrait;

    const RECURSION_MSG = 'Circular dependency detected. The product you are trying to link (SKU: %s) is already used to calculate stock for another product that you are trying to link this product to (SKU: %s).';

    public function __construct(Sql $readSql, Sql $fastReadSql, Sql $writeSql, Mapper $mapper)
    {
        parent::__construct($readSql, $fastReadSql, $writeSql, $mapper);
    }

    public function fetch($id)
    {
        $select = $this->getSelect()->where($this->getLinkIdWhere('parent', $id));
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLink not found with id %s', $id));
        }

        $array = null;
        foreach ($results as $data) {
            if (!is_array($array)) {
                $array = $this->toArray($data);
            } else {
                $this->appendStockRow($array, $data);
            }
        }
        return $this->mapper->fromArray($array);
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

        $collection = new Collection(ProductLink::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);
        foreach ($map as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }
        return $collection;
    }

    /**
     * @param ProductLink $entity
     */
    protected function saveEntity($entity)
    {
        try {
            $linkId = $this->getLinkId($entity->getOrganisationUnitId(), $entity->getProductSku());
            $this->removeLinkPaths($linkId);
            $this->removeEmptyPaths();
        } catch (NotFound $exception) {
            $linkId = $this->insertLink($entity->getOrganisationUnitId(), $entity->getProductSku());
            $entity->setNewlyInserted(true);
        }

        $parentPaths = $this->getLinkPathIdMap('child', $linkId);
        $childPaths = $this->saveChildPaths($entity, $linkId);
        $this->extendParentPaths($parentPaths, $childPaths);
        $this->removeEmptyPaths();

        return $entity;
    }

    protected function saveChildPaths(ProductLink $entity, $linkId)
    {
        $paths = [];
        foreach ($entity->getStockSkuMap() as $sku => $qty) {
            try {
                $childId = $this->getLinkId($entity->getOrganisationUnitId(), $sku);
            } catch (NotFound $exception) {
                $childId = $this->insertLink($entity->getOrganisationUnitId(), $sku);
            }

            if ($linkId == $childId) {
                throw new RecursionException(
                    sprintf(static::RECURSION_MSG, $entity->getProductSku(), $sku)
                );
            }

            $newPaths = [];
            foreach ($this->getLinkPaths('link.linkId', $childId) as $linkPath) {
                $linkIds = [$linkId => true];

                $path = [['linkId' => $linkId, 'quantity' => 1, 'order' => ($order = 0)]];
                foreach ($linkPath as $linkNode) {
                    if (isset($linkIds[$linkNode['linkId']])) {
                        throw new RecursionException(
                            sprintf(static::RECURSION_MSG, $entity->getProductSku(), $this->getLinkSku($linkNode['linkId']))
                        );
                    }

                    $linkIds[$linkNode['linkId']] = true;
                    $path[] = ['linkId' => $linkNode['linkId'], 'quantity' => $qty * $linkNode['quantity'], 'order' => ++$order];
                }
                $newPaths[] = $path;
            }

            if (empty($newPaths)) {
                $newPaths[] = [
                    ['linkId' => $linkId, 'quantity' => 1, 'order' => 0],
                    ['linkId' => $childId, 'quantity' => $qty, 'order' => 1],
                ];
            }

            foreach ($newPaths as $path) {
                $this->insertLinkPath($path);
                $paths[] = $path;
            }
        }
        return $paths;
    }

    protected function extendParentPaths(array $parentPaths, array $childPaths)
    {
        foreach ($parentPaths as $pathId => $parentPath) {
            $linkPaths = $this->getLinkPaths('path.pathId', $pathId);
            $this->removePath($pathId);

            foreach ($childPaths as $path) {
                array_shift($path);
                $this->insertLinkPath(
                    array_merge($linkPaths[$pathId], array_map(
                        function ($path) use ($parentPath) {
                            $path['quantity'] *= $parentPath['quantity'];
                            $path['order'] += $parentPath['order'] + 1;
                            return $path;
                        },
                        $path
                    ))
                );
            }
        }
    }

    protected function insertLink($ouId, $sku)
    {
        $insert = $this->getInsert()->values(['organisationUnitId' => $ouId, 'sku' => $sku]);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        return $this->writeSql->getAdapter()->getDriver()->getLastGeneratedValue();
    }

    protected function insertLinkPath(array $path)
    {
        try {
            $pathId = $this->getNextPathId();
            foreach ($path as $node) {
                $insert = $this->getInsert('productLinkPath')->values(['pathId' => $pathId] + $node);
                $this->writeSql->prepareStatementForSqlObject($insert)->execute();
            }
        } catch (Conflict $e) {
            throw new Deadlock('pathId already used by another process', 0, $e);
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
        $this->removeEmptyPaths();
        $this->removeLink($linkId);
    }

    protected function removeLinkPaths($linkId)
    {
        $parentLinkPathIdMap = $this->getLinkPathIdMap('parent', $linkId);
        if (empty($parentLinkPathIdMap)) {
            return;
        }
        $this->removeParentPaths($parentLinkPathIdMap);

        $childLinkPathIdMap = $this->getLinkPathIdMap('child', $linkId);;
        if (empty($childLinkPathIdMap)) {
            return;
        }
        $this->removeChildDuplicatePaths($childLinkPathIdMap);
    }

    protected function removeParentPaths(array $parentLinkPathIdMap)
    {
        $delete = $this->getDelete('productLinkPath');
        foreach ($parentLinkPathIdMap as $pathId => $map) {
            $delete->where->orPredicate(
                (new Where())->equalTo('pathId', $pathId)->greaterThan('order', $map['order'])
            );
        }
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function removeChildDuplicatePaths(array $childLinkPathIdMap)
    {
        $duplicates = [];
        foreach ($childLinkPathIdMap as $pathId => $map) {
            $id = implode('-', [$map['parent'], $map['child'], $map['order']]);
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

    protected function removeEmptyPaths()
    {
        $select = $this->writeSql
            ->select(['from' => 'productLinkPath'])
            ->columns(['pathId'])
            ->join(
                ['to' => 'productLinkPath'],
                new Expression('? = ? AND ? != ?', ['from.pathId', 'to.pathId', 'from.order' ,'to.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                [],
                Select::JOIN_LEFT
            )
            ->where(['to.pathId' => null]);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return;
        }

        $paths = [];
        foreach ($results as $result) {
            $paths[] = $result['pathId'];
        }
        $this->removePath(...$paths);
    }

    protected function removePath(...$pathIds)
    {
        $delete = $this->getDelete('productLinkPath')->where(['pathId' => $pathIds]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function removeLink($linkId)
    {
        if (!empty($this->getLinkPathIdMap('child', $linkId))) {
            return;
        }

        $delete = $this->getDelete()->where(['linkId' => $linkId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function toArray(array $data)
    {
        return [
            'organisationUnitId' => $data['organisationUnitId'],
            'sku' => $data['productSku'],
            'stock' => [$data['stockSku'] => $data['quantity']],
        ];
    }

    protected function appendStockRow(array &$array, array $data)
    {
        $array['stock'][$data['stockSku']] = $data['quantity'];
    }

    protected function getLinkId($organisationUnitId, $sku)
    {
        $select = $this->writeSql
            ->select('productLink')
            ->columns(['linkId'])
            ->where(
                (new Where())
                    ->equalTo('organisationUnitId', $organisationUnitId)
                    ->like('sku', escapeLikeValue($sku))
            );

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

    protected function getLinkPathIdMap($lookup, $linkId)
    {
        $select = $this->writeSql
            ->select(['parent' => 'productLinkPath'])
            ->columns(['parent' => 'linkId', 'pathId' => 'pathId', 'order' => 'order'])
            ->join(
                ['child' => 'productLinkPath'],
                new Expression('? = ? AND (? + 1) = ?', ['parent.pathId', 'child.pathId', 'parent.order', 'child.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                ['quantity' => 'quantity', 'child' => 'linkId']
            )
            ->where([sprintf('%s.linkId', $lookup) => $linkId]);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return [];
        }

        $pathIdOrderMap = [];
        foreach ($results as $result) {
            $pathIdOrderMap[$result['pathId']] = [
                'parent' => $result['parent'],
                'child' => $result['child'],
                'quantity' => $result['quantity'],
                'order' => $result['order'],
            ];
        }
        return $pathIdOrderMap;
    }

    protected function getLinkPaths($lookup, $linkId)
    {
        $select = $this->writeSql
            ->select(['link' => 'productLink'])
            ->columns([])
            ->join(
                ['path' => 'productLinkPath'],
                new Expression('? = ? AND ? = 0', ['link.linkId', 'path.linkId', 'path.order'], array_fill(0, 3, Expression::TYPE_IDENTIFIER)),
                []
            )
            ->join(
                ['paths' => 'productLinkPath'],
                'path.pathId = paths.pathId',
                ['pathId', 'linkId', 'quantity', 'order']
            )
            ->where([$lookup => $linkId])
            ->order(['paths.pathId', 'paths.order']);

        $results = $this->writeSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            return [];
        }

        $paths = [];
        foreach ($results as $result) {
            if (!isset($paths[$result['pathId']])) {
                $paths[$result['pathId']] = [];
            }
            $paths[$result['pathId']][] = [
                'linkId' => $result['linkId'],
                'quantity' => $result['quantity'],
                'order' => $result['order'],
            ];
        }
        return $paths;
    }

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select(['parent' => 'productLink'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
                'id' => 'linkId',
                'organisationUnitId' => 'organisationUnitId',
                'productSku' => 'sku',
            ])
            ->join(
                ['from' => 'productLinkPath'],
                new Expression('? = ? AND ? = 0', ['parent.linkId', 'from.linkId', 'from.order'], array_fill(0, 3, Expression::TYPE_IDENTIFIER)),
                []
            )
            ->join(
                ['to' => 'productLinkPath'],
                new Expression('? = ? AND (? + 1) = ?', ['from.pathId', 'to.pathId', 'from.order', 'to.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                ['quantity' => 'quantity']
            )
            ->join(
                ['child' => 'productLink'],
                'to.linkId = child.linkId',
                ['stockSku' => 'sku']
            );
    }

    protected function getFilteredSelect(Filter $filter, &$total = null)
    {
        $select = $this->getSelect();
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

        return $this->getSelect()->where(['parent.linkId' => $linkIds]);
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
            $select->where(['parent.organisationUnitId' => $organisationUnitId]);
        }
        if (!empty($productSku = $filter->getProductSku())) {
            $this->filterArrayValuesToOrdLikes('parent.sku', $productSku, $select->where);
        }
        if (!empty($stockSku = $filter->getStockSku())) {
            $this->filterArrayValuesToOrdLikes('child.sku', $stockSku, $select->where);
        }
        if (!empty($ouIdProductSkus = $filter->getOuIdProductSku())) {
            $select->where->addPredicate($this->getLinkIdWhere('parent', ...$ouIdProductSkus));
        }
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