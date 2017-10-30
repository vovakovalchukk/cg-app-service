<?php
namespace CG\Product\Graph\Storage;

use CG\Product\Graph\Collection;
use CG\Product\Graph\Entity as ProductGraph;
use CG\Product\Graph\Filter;
use CG\Product\Graph\Mapper;
use CG\Product\Graph\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\Stdlib\Storage\Db\FilterArrayValuesToOrdLikesTrait;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Db implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;
    use FilterArrayValuesToOrdLikesTrait;

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
        $select = $this->getSelect($this->getLinkIdSelect($id));
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductGraph not found with id %s', $id));
        }

        $array = $this->toArray($id);
        foreach ($results as $data) {
            $this->appendNodeRow($array, $data);
        }
        return $this->mapper->fromArray($array);
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $linkIdSelect = $this->getLinkIdSelect(...$filter->getOuIdProductSku());
        $total = $this->getTotal($linkIdSelect);

        if (($limit = $filter->getLimit()) !== 'all') {
            $linkIdSelect->limit($limit)->offset(($filter->getPage() - 1) * $limit);
        }

        $select = $this->getSelect($linkIdSelect);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound('No ProductGraph found matching filter');
        }

        $map = [];
        foreach ($results as $data) {
            $id = $data['id'];
            if (!isset($map[$id])) {
                $map[$id] = $this->toArray($id);
            }
            $this->appendNodeRow($map[$id], $data);
        }

        $collection = new Collection(ProductGraph::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);

        foreach ($map as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }

        return $collection;
    }

    protected function getTotal(Select $linkIdSelect): int
    {
        $select = clone $linkIdSelect;
        $select->columns(['count' => new Expression('COUNT(?)', ['linkId'], [Expression::TYPE_IDENTIFIER])]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['count'];
        }
        return 0;
    }

    protected function toArray($ouIdProductSku): array
    {
        [$organisationUnitId, $productSku] = explode('-', $ouIdProductSku, 2);
        return [
            'organisationUnitId' => $organisationUnitId,
            'sku' => $productSku,
            'nodes' => [],
        ];
    }

    protected function appendNodeRow(array &$array, array $data)
    {
        $array['nodes'][$data['sku']] = $data['quantity'];
    }

    protected function getLinkIdSelect(...$ouIdProductSkus): Select
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        foreach ($ouIdProductSkus as $ouIdProductSku) {
            [$organisationUnitId, $productSku] = explode('-', $ouIdProductSku, 2);
            $where->addPredicate(
                (new Where())->addPredicates(['organisationUnitId' => $organisationUnitId, 'sku' => $productSku])
            );
        }
        return $this->readSql->select('productLink')->columns(['linkId'])->where($where);
    }

    protected function getSelect(Select $linkIdSelect): Select
    {
        $paths = $this->readSql
            ->select(['link' => 'productLink'])
            ->columns([
                'id' => new Expression('CONCAT_WS(?, ?, ?)', ['-', 'link.organisationUnitId', 'link.sku'], [Expression::TYPE_VALUE, Expression::TYPE_IDENTIFIER, Expression::TYPE_IDENTIFIER]),
            ])
            ->join(
                ['path' => 'productLinkPath'],
                new Expression('? IN (?, ?)', ['link.linkId', 'path.from', 'path.to'], array_fill(0, 3, Expression::TYPE_IDENTIFIER)),
                [
                    'from' => 'from',
                    'to' => 'to',
                    'order' => new Expression('MAX(?)', ['path.order'], [Expression::TYPE_IDENTIFIER]),
                ]
            )
            ->join(
                ['links' => $linkIdSelect],
                'link.linkId = links.linkId',
                []
            )
            ->group(['id', 'path.from', 'path.to']);

        $lookup = $this->readSql
            ->select(['path' => 'productLinkPath'])
            ->columns(['pathId'])
            ->join(
                ['paths' => $paths],
                'path.from = paths.from AND path.to = paths.to AND path.order = paths.order',
                ['id']
            );

        $graph = $this->readSql
            ->select(['link' => 'productLink'])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['organisationUnitId', 'sku'])
            ->join(
                ['path' => 'productLinkPath'],
                new Expression('IF(? = 0, ? IN (?, ?), link.linkId = path.to)', ['path.order', 'link.linkId', 'path.from', 'path.to'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                [
                    'quantity' => new Expression('IF(? = 0 AND ? = ?, 1, ?)', ['path.order', 'link.linkId', 'path.from', 'path.quantity'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                    'order' => new Expression('IF(? = 0 AND ? = ?, 0, ? + 1)', ['path.order', 'link.linkId', 'path.from', 'path.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                ]
            )
            ->join(
                ['lookup' => $lookup],
                'path.pathId = lookup.pathId',
                ['id']
            );

        return $this->readSql
            ->select(['graph' => $graph])
            ->columns([
                'id' => 'id',
                'organisationUnitId' => 'organisationUnitId',
                'sku' => 'sku',
                'quantity' => new Expression('SUM(?)', ['graph.quantity'], [Expression::TYPE_IDENTIFIER])
            ])
            ->group(['graph.id', 'graph.organisationUnitId', 'graph.sku']);
    }
}