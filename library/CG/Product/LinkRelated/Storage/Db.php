<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Product\Link\Storage\DbLinkIdTrait;
use CG\Product\LinkRelated\Collection;
use CG\Product\LinkRelated\Entity as LinkRelated;
use CG\Product\LinkRelated\Filter;
use CG\Product\LinkRelated\Mapper;
use CG\Product\LinkRelated\StorageInterface;
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
            throw new NotFound(sprintf('ProductLinkRelated not found with id %s', $id));
        }

        ['organisationUnitId' => $ouId, 'sku' => $sku] = $results->current();
        return $this->mapper->fromArray($this->toArray($ouId, $sku, $this->getRelatedSkus($results)));
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
            throw new NotFound('No ProductLinkRelated found matching filter');
        }

        $map = [];
        foreach ($results as $data) {
            $id = $data['id'];
            $map[$id] = $map[$id] ?? [
                'organisationUnitId' => $data['organisationUnitId'],
                'sku' => $data['sku'],
                'relatedSkus' => [],
            ];
            $map[$id]['relatedSkus'][] = $data['relatedSku'];
        }

        $collection = new Collection(LinkRelated::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);

        foreach ($map as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }

        return $collection;
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

    protected function getRelatedSkus(ResultInterface $results): array
    {
        $relatedSkusMap = [];
        foreach ($results as $data) {
            $relatedSkusMap[] = $data['relatedSku'];
        }
        return $relatedSkusMap;
    }

    protected function toArray($organisationUnitId, $sku, array $relatedSkus): array
    {
        return [
            'organisationUnitId' => $organisationUnitId,
            'sku' => $sku,
            'relatedSkus' => $relatedSkus,
        ];
    }

    protected function getSelect(Select $linkIdSelect): Select
    {
        return $this->readSql
            ->select(['search' => $linkIdSelect])->columns(['id' => 'linkId', 'organisationUnitId', 'sku'])->quantifier(Select::QUANTIFIER_DISTINCT)
            ->join(['searchLeafPath' => 'productLinkPath'], 'search.linkId = searchLeafPath.linkId', [])
            ->join(
                ['relatedMinPath' => 'productLinkPath'],
                new Expression('? = ? AND ? = 0', ['searchLeafPath.pathId', 'relatedMinPath.pathId', 'relatedMinPath.order'], array_fill(0, 3, Expression::TYPE_IDENTIFIER)),
                []
            )
            ->join(['relatedMinPaths' => 'productLinkPath'], 'relatedMinPath.linkId = relatedMinPaths.linkId', [])
            ->join(['productLinkPathMax' => $this->getMaxOrderSelect()], 'relatedMinPaths.pathId = productLinkPathMax.pathId', [])
            ->join(['relatedMaxPath' => 'productLinkPath'], 'productLinkPathMax.pathId = relatedMaxPath.pathId AND productLinkPathMax.order = relatedMaxPath.order', [])
            ->join(['relatedRootPath' => 'productLinkPath'], 'relatedMaxPath.linkId = relatedRootPath.linkId', [])
            ->join(['relatedLeafPath' => 'productLinkPath'], 'relatedRootPath.pathId = relatedLeafPath.pathId', [])
            ->join(['related' => 'productLink'], 'relatedLeafPath.linkId = related.linkId', ['relatedSku' => 'sku']);
    }

    protected function getMaxOrderSelect(): Select
    {
        return $this->readSql
            ->select('productLinkPath')
            ->columns(['pathId', 'order' => new Expression('MAX(?)', ['order'], [Expression::TYPE_IDENTIFIER])])
            ->group(['pathId']);
    }
}