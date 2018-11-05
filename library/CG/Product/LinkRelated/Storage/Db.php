<?php
namespace CG\Product\LinkRelated\Storage;

use CG\Product\LinkRelated\Entity as LinkRelated;
//use CG\Product\LinkRelated\Filter;
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

        $where = new Where();
        $where->equalTo('l1.organisationUnitId', $ouId)
            ->equalTo('l1.sku', $sku);

        $select = $this->getSelect()->where($where);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLinkNode not found with id %s', $id));
        }

        $array = null;
        foreach ($results as $data) {
            if (!is_array($array)) {
                $array = $this->toArray($data);
            }
            $this->appendNode($array, $data);
        }
        return $this->mapper->fromArray($array);
    }

    public function invalidate($id)
    {
        // NoOp - Data is calculated based on productLinks, nothing to invalidate
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

    protected function toArray(array $data)
    {
        return [
            'organisationUnitId' => $data['organisationUnitId'],
            'sku' => $data['productSku'],
            'ancestors' => [],
            'descendants' => [],
        ];
    }

    protected function getLinkIdSelect(...$ouIdProductSkus): Select
    {
        $where = new Where(null, Where::COMBINED_BY_OR);
        foreach ($ouIdProductSkus as $ouIdProductSku) {
            [$organisationUnitId, $productSku] = array_pad(explode('-', $ouIdProductSku, 2), 2, '');
            $where->addPredicate(
                (new Where())
                    ->equalTo('organisationUnitId', $organisationUnitId)
                    ->equalTo('sku', escapeLikeValue($productSku))
            );
        }
        return $this->readSql->select('productLink')->columns(['linkId', 'organisationUnitId', 'sku'])->where($where);
    }

    protected function getSelect(Select $linkIdSelect): Select
    {
//        SELECT DISTINCT l4.`sku`
//        FROM productLink l1
//        JOIN productLinkPath p1 ON l1.`linkId` = p1.`linkId`
//        JOIN productLinkPath p2 ON p1.`pathId` = p2.`pathId`
//        JOIN productLinkPath p3 ON p2.`linkId` = p3.`linkId`
//        JOIN productLinkPath p4 ON p3.`pathId` = p4.`pathId`
//        JOIN productLink l4 ON p4.`linkId` = l4.`linkId`
//        WHERE l1.`organisationUnitId` = 66 AND l1.`sku` LIKE "snack/1bag"

        return $this->readSql
            ->select()
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->from(['l1' => 'productLink'])
            ->columns('sku')
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
                []
            );




//        return $this->readSql
//            ->select(['lookup' => $linkIdSelect])
//            ->quantifier(Select::QUANTIFIER_DISTINCT)
//            ->columns([
//                'id' => 'linkId',
//                'organisationUnitId' => 'organisationUnitId',
//                'productSku' => 'sku',
//            ])
//            ->join(
//                ['path' => 'productLinkPath'],
//                'lookup.linkId = path.linkId',
//                []
//            )
//            ->join(
//                ['paths' => 'productLinkPath'],
//                new Expression('? = ? AND ? != ?', ['path.pathId', 'paths.pathId', 'path.order', 'paths.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
//                ['ancestor' => new Expression('? > ?', ['path.order', 'paths.order'], array_fill(0, 2, Expression::TYPE_IDENTIFIER))]
//            )
//            ->join(
//                ['node' => 'productLink'],
//                'paths.linkId = node.linkId',
//                ['node' => 'sku']
//            );
    }
}