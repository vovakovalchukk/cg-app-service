<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Product\LinkRelated\Mapper;
use CG\Product\LinkRelated\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use function CG\Stdlib\escapeLikeValue;

class Db implements StorageInterface, LoggerAwareInterface
{
    use LogTrait;

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
        [$ouId, $sku] = explode('-', $id, 2);

        $where = (new Where())
            ->equalTo('search.organisationUnitId', $ouId)
            ->like('search.sku', escapeLikeValue($sku));

        $select = $this->getSelect()->where($where);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLinkRelated not found with id %s', $id));
        }

        return $this->mapper->fromArray($this->toArray($ouId, $sku, $this->getRelatedSkus($results)));
    }

    protected function getRelatedSkus($results): array
    {
        $relatedSkusMap = [];
        foreach ($results as $data) {
            $relatedSkusMap[] = $data['sku'];
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
            ->select(['search' => 'productLink'])->columns([])->quantifier(Select::QUANTIFIER_DISTINCT)
            ->join(['searchLeafPath' => 'productLinkPath'], 'search.linkId = searchLeafPath.linkId', [])
            ->join(['relatedMinPath' => 'productLinkPath'], new Expression('searchLeafPath.pathId = relatedMinPath.pathId AND relatedMinPath.order = ?', [0]), [])
            ->join(['relatedMinPaths' => 'productLinkPath'], 'relatedMinPath.linkId = relatedMinPaths.linkId', [])
            ->join(['productLinkPathMax' => $this->getMaxOrderSelect()], 'relatedMinPaths.pathId = productLinkPathMax.pathId', [])
            ->join(['relatedMaxPath' => 'productLinkPath'], 'productLinkPathMax.pathId = relatedMaxPath.pathId AND productLinkPathMax.order = relatedMaxPath.order', [])
            ->join(['relatedRootPath' => 'productLinkPath'], 'relatedMaxPath.linkId = relatedRootPath.linkId', [])
            ->join(['relatedLeafPath' => 'productLinkPath'], 'relatedRootPath.pathId = relatedLeafPath.pathId', [])
            ->join(['related' => 'productLink'], 'relatedLeafPath.linkId = related.linkId', ['sku']);
    }

    protected function getMaxOrderSelect(): Select
    {
        return $this->readSql
            ->select('productLinkPath')
            ->columns(['pathId', 'order' => new Expression('MAX(?)', ['order'], [Expression::TYPE_IDENTIFIER])])
            ->group(['pathId']);
    }
}