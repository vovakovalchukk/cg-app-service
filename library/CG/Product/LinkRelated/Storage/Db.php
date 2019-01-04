<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Product\Link\Storage\DbLinkIdTrait;
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
        $select = $this->getSelect()->where($this->getLinkIdWhere('search', $id));
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLinkRelated not found with id %s', $id));
        }

        ['organisationUnitId' => $ouId, 'sku' => $sku] = $results->current();
        return $this->mapper->fromArray($this->toArray($ouId, $sku, $this->getRelatedSkus($results)));
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

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select(['search' => 'productLink'])->columns(['organisationUnitId', 'sku'])->quantifier(Select::QUANTIFIER_DISTINCT)
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