<?php
namespace CG\Amazon\Product\CategoryDetail\External\Storage;

use CG\Amazon\Product\CategoryDetail\External;
use CG\Amazon\Product\CategoryDetail\External\StorageInterface;
use CG\Product\CategoryDetail\ExternalInterface;
use CG\Stdlib\Storage\Db\DeadlockHandlerTrait;
use CG\Stdlib\Storage\Db\Zend\TransactionTrait;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Stdlib\ArrayUtils;

class Db implements StorageInterface
{
    use DeadlockHandlerTrait;
    use TransactionTrait;

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
        $select = $this->getProductCategoryDetailSelect()->where([
            'productId' => $productId,
            'categoryId' => $categoryId
        ]);
        $detail = $this->readSql->prepareStatementForSqlObject($select)->execute();

        $select = $this->getItemSpecificsSelect()->where([
            'productId' => $productId,
            'categoryId' => $categoryId
        ]);
        $itemSpecifics = $this->readSql->prepareStatementForSqlObject($select)->execute();

        $array = $this->mapResultsToArray($detail, $itemSpecifics);

        return External::fromArray($array[$productId][$categoryId] ?? []);
    }

    public function fetchMultiple(array $ids): array
    {
        $select = $this->getProductCategoryDetailSelect();
        foreach ($ids as $id) {
            [$productId, $categoryId] = $id;
            $select->where->orPredicate(
                (new Where())
                    ->equalTo('productCategoryAmazonDetail.productId', $productId)
                    ->equalTo('productCategoryAmazonDetail.categoryId', $categoryId)
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

    protected function mapResultsToArray(ResultInterface $details, ResultInterface $itemSpecifics): array
    {
        $results = [];
        $results = $this->attachDetailsToResults($results, $details);
//        $results = $this->attachItemSpecificsToResults($results, $itemSpecifics);

        return $results;

        die;
        $array = [];
        foreach ($results as $result) {
            $productId = $result['productId'];
            $categoryId = $result['categoryId'];
            if (!isset($array[$productId])) {
                $array[$productId] = [];
            }
            if (!isset($array[$productId][$categoryId])) {
                $array[$productId][$categoryId] = [
                    'subCategoryId' => $result['subCategoryId'],
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
        print_r($array);die;
        return $array;
    }

    protected function attachDetailsToResults(array $results, ResultInterface $details): array
    {
        foreach ($details as $detail) {
            $productId = $detail['productId'];
            $categoryId = $detail['categoryId'];
            if (!isset($results[$productId])) {
                $results[$productId] = [$categoryId => []];
            }
            $results[$productId][$categoryId]['subCategoryId'] = $detail['subCategoryId'];
        }

        return $results;
    }

    protected function attachItemSpecificsToResults(array $results, ResultInterface $itemSpecifics): array
    {
        $itemSpecifics = ArrayUtils::iteratorToArray($itemSpecifics);
        $productCategories = $this->extractProductCategoryForItemSpecific($itemSpecifics);
        print_r($productCategories);die;
        foreach ($itemSpecifics as $itemSpecific) {
            $productId = $itemSpecific['productId'];
            $categoryId = $itemSpecific['categoryId'];
            if (!isset($results[$productId])) {
                $results[$productId] = [$categoryId => []];
            }
        }
        return $results;
    }

    protected function extractProductCategoryForItemSpecific(array $itemSpecifics): array
    {
        $productCategory = [];
        foreach ($itemSpecifics as $itemSpecific) {
            $productId = $itemSpecific['productId'];
            $categoryId = $itemSpecific['categoryId'];
            if (!isset($productCategory[$productId])) {
                $productCategory[$productId] = [];
            }
            $productCategory[$productId][$categoryId] = $categoryId;
        }
        var_dump($productCategory);
        die;
    }

    public function save(int $productId, int $categoryId, ExternalInterface $external): void
    {
        $this->startTransactionAndHandleDeadlock([$this, 'saveExternalData'], func_get_args());
    }

    protected function saveExternalData(int $productId, int $categoryId, ExternalInterface $external): void
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

        $this->insertItemSpecifics($itemSpecifics, $productId, $categoryId);
    }

    protected function insertItemSpecifics(
        array $itemSpecifics,
        int $productId,
        int $categoryId,
        int $parentId = 0
    ): void {
        foreach ($itemSpecifics as $name => $values) {
            $itemSpecificId = $this->insertItemSpecificName($name, $productId, $categoryId, $parentId);
            if (is_array($values) && $this->isAssociativeArray($values)) {
                $this->insertItemSpecifics($values, $productId, $categoryId, $itemSpecificId);
                continue;
            }
            $this->insertItemSpecificValues($itemSpecificId, (array) $values);
        }
    }

    protected function insertItemSpecificName(string $name, int $productId, int $categoryId, int $parentId): int
    {
        $insert = $this->getInsert('productCategoryAmazonItemSpecifics')->values([
            'productId' => $productId,
            'categoryId' => $categoryId,
            'parentId' => $parentId,
            'name' => $name
        ]);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        return $this->writeSql->getAdapter()->getDriver()->getLastGeneratedValue();
    }

    protected function insertItemSpecificValues(int $id, array $values): void
    {
        foreach ($values as $value) {
            $insert = $this->getInsert('productCategoryAmazonItemSpecificsValues')->values([
                'itemSpecificId' => $id,
                'value' => $value
            ]);
            $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        }
    }

    protected function isAssociativeArray(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }


    public function remove(int $productId, int $categoryId): void
    {
        $delete = $this->getDelete()->where([
            'productCategoryAmazonDetail.productId' => $productId,
            'productCategoryAmazonDetail.categoryId' => $categoryId,
        ]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getProductCategoryDetailSelect(): Select
    {
        return $this->readSql->select('productCategoryAmazonDetail');
    }

    protected function getItemSpecificsSelect(): Select
    {
        return $this->readSql
            ->select('productCategoryAmazonItemSpecifics')
            ->columns(['id', 'productId', 'categoryId', 'name', 'parentId'])
            ->join(
                'productCategoryAmazonItemSpecificsValues',
                'productCategoryAmazonItemSpecifics.id = productCategoryAmazonItemSpecificsValues.itemSpecificId',
                ['value'],
                Select::JOIN_LEFT
            );
    }

    protected function getInsert(string $table = 'productCategoryAmazonDetail'): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete(): Delete
    {
        return $this->writeSql->delete('productCategoryAmazonDetail');
    }

    // Required by traits
    protected function getWriteSql()
    {
        return $this->writeSql;
    }
}