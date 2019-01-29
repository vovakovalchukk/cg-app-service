<?php
namespace CG\Product\LinkNode\Storage;

use CG\Product\Link\Storage\DbLinkIdTrait;
use CG\Product\LinkNode\Collection;
use CG\Product\LinkNode\Entity as LinkNode;
use CG\Product\LinkNode\Filter;
use CG\Product\LinkNode\Mapper;
use CG\Product\LinkNode\StorageInterface;
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
        $select = $this->getSelect($this->getLinkIdSelect($id));
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
            throw new NotFound('No ProductLinkNode found matching filter');
        }

        $map = [];
        foreach ($results as $data) {
            $id = $data['id'];
            if (!isset($map[$id])) {
                $map[$id] = $this->toArray($data);
            }
            $this->appendNode($map[$id], $data);
        }

        $collection = new Collection(LinkNode::class, __FUNCTION__, $filter->toArray());
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

    protected function toArray(array $data)
    {
        return [
            'organisationUnitId' => $data['organisationUnitId'],
            'sku' => $data['productSku'],
            'ancestors' => [],
            'descendants' => [],
        ];
    }

    protected function appendNode(array &$array, array $data)
    {
        $type = $data['ancestor'] ? 'ancestors' : 'descendants';
        $array[$type][] = $data['node'];
    }

    protected function getLinkIdSelect(...$ouIdProductSkus): Select
    {
        return $this->readSql
            ->select('productLink')
            ->columns(['linkId', 'organisationUnitId', 'sku'])
            ->where($this->getLinkIdWhere(null, ...$ouIdProductSkus));
    }

    protected function getSelect(Select $linkIdSelect): Select
    {
        return $this->readSql
            ->select(['lookup' => $linkIdSelect])
            ->quantifier(Select::QUANTIFIER_DISTINCT)
            ->columns([
                'id' => 'linkId',
                'organisationUnitId' => 'organisationUnitId',
                'productSku' => 'sku',
            ])
            ->join(
                ['path' => 'productLinkPath'],
                'lookup.linkId = path.linkId',
                []
            )
            ->join(
                ['paths' => 'productLinkPath'],
                new Expression('? = ? AND ? != ?', ['path.pathId', 'paths.pathId', 'path.order', 'paths.order'], array_fill(0, 4, Expression::TYPE_IDENTIFIER)),
                ['ancestor' => new Expression('? > ?', ['path.order', 'paths.order'], array_fill(0, 2, Expression::TYPE_IDENTIFIER))]
            )
            ->join(
                ['node' => 'productLink'],
                'paths.linkId = node.linkId',
                ['node' => 'sku']
            );
    }
}