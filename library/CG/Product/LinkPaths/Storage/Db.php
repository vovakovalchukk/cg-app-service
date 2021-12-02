<?php
namespace CG\Product\LinkPaths\Storage;

use CG\Product\Link\Storage\DbLinkIdTrait;
use CG\Product\Link\Storage\DbMaxOrderSelectTrait;
use CG\Product\Link\Storage\LogMatchingLinkIdsOnNotFoundTrait;
use CG\Product\LinkPaths\Collection;
use CG\Product\LinkPaths\Entity as LinkPaths;
use CG\Product\LinkPaths\Filter;
use CG\Product\LinkPaths\Mapper;
use CG\Product\LinkPaths\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class Db implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;
    use DbLinkIdTrait;
    use DbMaxOrderSelectTrait;
    use LogMatchingLinkIdsOnNotFoundTrait;

    protected const LOG_CODE = 'LinkPathsStorageDb';

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
        $select = $this->getSelect($this->getLinkIdSelect($id), $id);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        if ($results->count() == 0) {
            $this->logMatchingLinkIdsOnNotFound($this->readSql, $this->getLinkIdSelect($id), $select);
            throw new NotFound(sprintf('ProductLinkPaths not found with id %s', $id));
        }

        $arrays = $this->toArrays($results);
        return $this->mapper->fromArray(reset($arrays));
    }

    public function invalidate($id)
    {
        // NoOp - Data is calculated based on productLinks, nothing to invalidate
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $linkIdSelect = $this->getLinkIdSelect(...$filter->getOuIdProductSku());
        $total = $this->getTotal($linkIdSelect);

        if (($limit = $filter->getLimit()) !== 'all') {
            $linkIdSelect->limit($limit)->offset(($filter->getPage() - 1) * $limit);
        }

        $select = $this->getSelect($linkIdSelect, ...$filter->getOuIdProductSku());
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound('No ProductLinkPaths found matching filter');
        }

        $collection = new Collection(LinkPaths::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);

        foreach ($this->toArrays($results) as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }

        return $collection;
    }

    protected function toArrays(ResultInterface $results): array
    {
        $arrays = [];
        foreach ($results as $data) {
            $id = $data['id'];
            $arrays[$id] = $arrays[$id] ?? [
                'organisationUnitId' => $data['organisationUnitId'],
                'sku' => $data['sku'],
                'paths' => [],
            ];

            $pathId = $data['pathId'];
            $arrays[$id]['paths'][$pathId] = $arrays[$id]['paths'][$pathId] ?? [];
            $arrays[$id]['paths'][$pathId][$data['link']] = $data['quantity'];
        }
        return $arrays;
    }

    protected function getTotal(Select $linkIdSelect): int
    {
        $select = (clone $linkIdSelect)
            ->reset(Select::QUANTIFIER)
            ->columns([
                'count' => new Expression(
                    'COUNT(? ?)',
                    [Select::QUANTIFIER_DISTINCT, 'productLink.linkId'],
                    [Expression::TYPE_LITERAL, Expression::TYPE_IDENTIFIER]
                ),
            ]);

        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();
        foreach ($results as $result) {
            return $result['count'];
        }
        return 0;
    }

    protected function getLinkIdSelect(...$ouIdProductSkus): Select
    {
        return $this->readSql
            ->select('productLink')
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns(['linkId', 'organisationUnitId', 'sku'])
            ->join('productLinkPath', 'productLink.linkId = productLinkPath.linkId', [])
            ->where($this->getLinkIdWhere('productLink', ...$ouIdProductSkus));
    }

    protected function getSelect(Select $linkIdSelect, ...$ouIdProductSkus): Select
    {
        return $this->readSql
            ->select(['search' => $linkIdSelect])->columns(['id' => 'linkId', 'organisationUnitId', 'sku'])->quantifier(Select::QUANTIFIER_DISTINCT)
            ->join(['searchLeafPath' => 'productLinkPath'], 'search.linkId = searchLeafPath.linkId', [])
            ->join(['productLinkPathMax' => $this->getMaxOrderSelect($this->readSql, ...$ouIdProductSkus)], 'searchLeafPath.pathId = productLinkPathMax.pathId', [])
            ->join(['relatedPath' => 'productLinkPath'], 'productLinkPathMax.pathId = relatedPath.pathId AND productLinkPathMax.order = relatedPath.order', [])
            ->join(['relatedRootPath' => 'productLinkPath'], 'relatedPath.linkId = relatedRootPath.linkId', [])
            ->join(['relatedLeafPath' => 'productLinkPath'], 'relatedRootPath.pathId = relatedLeafPath.pathId', ['pathId', 'quantity'])
            ->join(['related' => 'productLink'], 'relatedLeafPath.linkId = related.linkId', ['link' => 'sku'])
            ->order([
                'relatedRootPath.linkId' => Select::ORDER_ASCENDING,
                'relatedLeafPath.pathId' => Select::ORDER_ASCENDING,
                'relatedLeafPath.order' => Select::ORDER_DESCENDING
            ]);
    }

    protected function getLogCode(): string
    {
        return static::LOG_CODE;
    }
}