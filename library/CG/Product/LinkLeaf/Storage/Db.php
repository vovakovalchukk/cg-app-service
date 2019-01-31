<?php
namespace CG\Product\LinkLeaf\Storage;

use CG\Product\Link\Storage\DbLinkIdTrait;
use CG\Product\LinkLeaf\Collection;
use CG\Product\LinkLeaf\Entity as LinkLeaf;
use CG\Product\LinkLeaf\Filter;
use CG\Product\LinkLeaf\Mapper;
use CG\Product\LinkLeaf\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
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
        $select = $this->getSelect($this->getPathIdSelect($id));
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound(sprintf('ProductLinkLeaf not found with id %s', $id));
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

    public function invalidate($id)
    {
        // NoOp - Data is calculated based on productLinks, nothing to invalidate
    }

    public function fetchCollectionByFilter(Filter $filter)
    {
        $pathIdSelect = $this->getPathIdSelect(...$filter->getOuIdProductSku());
        $total = $this->getTotal($pathIdSelect);

        if (($limit = $filter->getLimit()) !== 'all') {
            $pathIdSelect->limit($limit)->offset(($filter->getPage() - 1) * $limit);
        }

        $select = $this->getSelect($pathIdSelect);
        $results = $this->readSql->prepareStatementForSqlObject($select)->execute();

        if ($results->count() == 0) {
            throw new NotFound('No ProductLinkLeaf found matching filter');
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

        $collection = new Collection(LinkLeaf::class, __FUNCTION__, $filter->toArray());
        $collection->setTotal($total);

        foreach ($map as $array) {
            $collection->attach($this->mapper->fromArray($array));
        }

        return $collection;
    }

    protected function getTotal(Select $pathIdSelect): int
    {
        $select = clone $pathIdSelect;
        $select->columns([
            'count' => new Expression(
                'COUNT(? ?)',
                [Select::QUANTIFIER_DISTINCT, 'link.linkId'],
                [Expression::TYPE_LITERAL, Expression::TYPE_IDENTIFIER]
            )
        ]);

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
            'stock' => [$data['stockSku'] => $data['quantity']],
        ];
    }

    protected function appendStockRow(array &$array, array $data)
    {
        $array['stock'][$data['stockSku']] = $data['quantity'];
    }

    protected function getPathIdSelect(...$ouIdProductSkus): Select
    {
        return $this->readSql
            ->select(['path' => 'productLinkPath'])
            ->columns(['pathId', 'linkId'])
            ->join(
                ['link' => 'productLink'],
                new Expression('? = ? AND ? = 0', ['path.linkId', 'link.linkId', 'path.order'], array_fill(0, 3, Expression::TYPE_IDENTIFIER)),
                []
            )
            ->where($this->getLinkIdWhere('link', ...$ouIdProductSkus));
    }

    protected function getSelect(Select $pathIdSelect): Select
    {
        $lookup = $this->readSql
            ->select(['result' => 'productLinkPath'])
            ->columns(['pathId' => 'pathId', 'order' => new Expression('MAX(?)', ['result.order'], [Expression::TYPE_IDENTIFIER])])
            ->join(
                ['lookup' => $pathIdSelect],
                'result.pathId = lookup.pathId',
                ['linkId']
            )
            ->group(['result.pathId']);

        return $this->readSql
            ->select(['lookup' => $lookup])
            ->columns(['id' => 'linkId'])
            ->join(
                ['from' => 'productLink'],
                'lookup.linkId = from.linkId',
                ['organisationUnitId' => 'organisationUnitId', 'productSku' => 'sku']
            )
            ->join(
                ['path' => 'productLinkPath'],
                'lookup.pathId = path.pathId AND lookup.order = path.order',
                ['quantity' => new Expression('SUM(?)', ['path.quantity'], [Expression::TYPE_IDENTIFIER])]
            )
            ->join(
                ['to' => 'productLink'],
                'path.linkId = to.linkId',
                ['stockSku' => 'sku']
            )
            ->group(['lookup.linkId', 'path.linkId']);
    }
}