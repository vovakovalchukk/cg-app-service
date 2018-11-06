<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Product\LinkRelated\Mapper;
use CG\Product\LinkRelated\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
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

        $where = new Where();
        $where
            ->equalTo('l1.organisationUnitId', $ouId)
            ->equalTo('l1.sku', escapeLikeValue($sku));

        $select = $this->getSelect()->where($where);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLinkRelated not found with id %s', $id));
        }

        return $this->mapper->fromArray($this->toArray($ouId, $sku, $this->getRelatedSkus($results)));
    }

    public function invalidate($id)
    {
        // NoOp - Data is calculated based on productLinks, nothing to invalidate
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
            ->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->from(['l1' => 'productLink'])
            ->columns(['organisationUnitId'])
            ->join(
                ['p1' => 'productLinkPath'],
                'l1.linkId = p1.linkId',
                []
            )
            ->join(
                ['p2' => 'productLinkPath'],
                'p1.pathId = p2.pathId',
                []
            )
            ->join(
                ['p3' => 'productLinkPath'],
                'p2.linkId = p3.linkId',
                []
            )
            ->join(
                ['p4' => 'productLinkPath'],
                'p3.pathId = p4.pathId',
                []
            )
            ->join(
                ['l2' => 'productLink'],
                'p4.linkId = l2.linkId',
                ['sku']
            );
    }
}