<?php
namespace CG\Ebay\Product\CategoryDetail\External\Storage;

use CG\Ebay\Product\CategoryDetail\External;
use CG\Ebay\Product\CategoryDetail\External\StorageInterface;
use CG\Product\CategoryDetail\ExternalInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class Db implements StorageInterface
{
    /** @var Sql */
    protected $readSql;
    /** @var Sql */
    protected $writeSql;

    public function __construct(Sql $readSql, Sql $writeSql)
    {
        $this->readSql = $readSql;
        $this->writeSql = $writeSql;
    }

    public function fetch(int $productId, int $categoryId): ExternalInterface
    {
        $select = $this->getSelect()->where([
            'productCategoryEbayDetail.productId' => $productId,
            'productCategoryEbayDetail.categoryId' => $categoryId,
        ]);
        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );
        return External::fromArray($array[$productId][$categoryId] ?? []);
    }

    public function fetchMultiple(array $ids): array
    {
        $select = $this->getSelect();
        foreach ($ids as $id) {
            [$productId, $categoryId] = $id;
            $select->where->orPredicate(
                (new Where())
                    ->equalTo('productCategoryEbayDetail.productId', $productId)
                    ->equalTo('productCategoryEbayDetail.categoryId', $categoryId)
            );
        }

        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );

        $externals = [];
        foreach ($ids as $id) {
            [$productId, $categoryId] = $id;
            if (!isset($externals[$productId])) {
                $externals[$productId] = [];
            }
            $externals[$productId][$categoryId] = External::fromArray($array[$productId][$categoryId] ?? []);
        }
        return $externals;
    }

    protected function mapResultsToArray(ResultInterface $results): array
    {
        $array = [];
        foreach ($results as $result) {
            $productId = $result['productId'];
            $categoryId = $result['categoryId'];
            if (!isset($array[$productId])) {
                $array[$productId] = [];
            }
            if (!isset($array[$productId][$categoryId])) {
                $array[$productId][$categoryId] = [
                    'listingDuration' => $result['listingDuration'],
                    'itemSpecifics' => [],
                ];
            }
            if (!isset($result['name'])) {
                continue;
            }
            if (isset($array[$productId][$categoryId]['itemSpecifics'][$result['name']])) {
                $array[$productId][$categoryId]['itemSpecifics'][$result['name']]
                    = (array) $array[$productId][$categoryId]['itemSpecifics'][$result['name']];
                $array[$productId][$categoryId]['itemSpecifics'][$result['name']][] = $result['value'];
            } else {
                $array[$productId][$categoryId]['itemSpecifics'][$result['name']] = $result['value'];
            }
        }
        return $array;
    }

    public function save(int $productId, int $categoryId, ExternalInterface $external): void
    {
        $this->remove($productId, $categoryId);

        $array = $external->toArray();
        $itemSpecifics = $array['itemSpecifics'] ?? [];
        unset($array['itemSpecifics']);

        $insert = $this->getInsert()->values(array_merge(
            ['productId' => $productId, 'categoryId' => $categoryId],
            $array
        ));
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();

        foreach ($itemSpecifics as $name => $values) {
            foreach (((array) $values) as $value) {
                $insert = $this->getInsert('productCategoryEbayItemSpecifics')->values([
                    'productId' => $productId,
                    'categoryId' => $categoryId,
                    'name' => $name,
                    'value' => $value,
                ]);
                $this->writeSql->prepareStatementForSqlObject($insert)->execute();
            }
        }
    }

    public function remove(int $productId, int $categoryId): void
    {
        $delete = $this->getDelete()->where([
            'productCategoryEbayDetail.productId' => $productId,
            'productCategoryEbayDetail.categoryId' => $categoryId,
        ]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select('productCategoryEbayDetail')
            ->join(
                'productCategoryEbayItemSpecifics',
                'productCategoryEbayDetail.productId = productCategoryEbayItemSpecifics.productId'
                . ' AND productCategoryEbayDetail.categoryId = productCategoryEbayItemSpecifics.categoryId',
                ['name', 'value'],
                Select::JOIN_LEFT
            );
    }

    protected function getInsert(string $table = 'productCategoryEbayDetail'): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete(): Delete
    {
        return $this->writeSql->delete('productCategoryEbayDetail');
    }
}