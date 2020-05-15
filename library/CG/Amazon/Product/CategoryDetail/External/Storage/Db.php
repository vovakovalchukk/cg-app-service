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
use function CG\Stdlib\isArrayAssociative;

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

        $select = $this->getValidValuesSelect()->where([
            'productId' => $productId,
            'categoryId' => $categoryId
        ]);
        $validValues = $this->readSql->prepareStatementForSqlObject($select)->execute();

        $array = $this->mapResultsToArray($detail, $itemSpecifics, $validValues);

        return External::fromArray($array[$productId][$categoryId] ?? []);
    }

    public function fetchMultiple(array $ids): array
    {
        $productDetailSelect = $this->getProductCategoryDetailSelect();
        $itemSpecificsSelect = $this->getItemSpecificsSelect();
        $validValuesSelect = $this->getValidValuesSelect();

        foreach ($ids as $id) {
            [$productId, $categoryId] = $id;
            $productDetailSelect->where->orPredicate(
                (new Where())
                    ->equalTo('productId', $productId)
                    ->equalTo('categoryId', $categoryId)
            );
            $itemSpecificsSelect->where->orPredicate(
                (new Where())
                    ->equalTo('productId', $productId)
                    ->equalTo('categoryId', $categoryId)
            );
            $validValuesSelect->where->orPredicate(
                (new Where())
                    ->equalTo('productId', $productId)
                    ->equalTo('categoryId', $categoryId)
            );
        }

        $details = $this->readSql->prepareStatementForSqlObject($productDetailSelect)->execute();
        $itemSpecifics = $this->readSql->prepareStatementForSqlObject($itemSpecificsSelect)->execute();
        $validValues = $this->readSql->prepareStatementForSqlObject($validValuesSelect)->execute();

        $array = $this->mapResultsToArray($details, $itemSpecifics, $validValues);

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

    protected function mapResultsToArray(
        ResultInterface $details,
        ResultInterface $itemSpecifics,
        ResultInterface $validValues
    ): array {
        $results = [];
        $results = $this->attachDetailsToResults($results, $details);
        $results = $this->attachItemSpecificsToResults($results, $itemSpecifics);
        $results = $this->attachValidValuesToResults($results, $validValues);
        return $results;
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
            $results[$productId][$categoryId]['variationTheme'] = $detail['variationTheme'];
            $results[$productId][$categoryId]['productType'] = $detail['productType'];
        }

        return $results;
    }

    protected function attachItemSpecificsToResults(array $results, ResultInterface $itemSpecifics): array
    {
        $itemSpecifics = ArrayUtils::iteratorToArray($itemSpecifics);
        foreach ($itemSpecifics as $itemSpecific) {
            $productId = $itemSpecific['productId'];
            $categoryId = $itemSpecific['categoryId'];
            if (!isset($results[$productId])) {
                $results[$productId] = [$categoryId => []];
            }
            if (isset($results[$productId][$categoryId]['itemSpecific'])) {
                continue;
            }
            $results[$productId][$categoryId]['itemSpecifics'] = $this->buildItemSpecificsArrayForProductAndCategory(
                $itemSpecifics,
                $productId,
                $categoryId
            );
        }
        return $results;
    }

    protected function buildItemSpecificsArrayForProductAndCategory(
        array $itemSpecifics,
        int $productId,
        int $categoryId
    ): array {
        $itemSpecifics = array_filter($itemSpecifics, function($itemSpecific) use ($productId, $categoryId) {
            return $itemSpecific['productId'] == $productId && $itemSpecific['categoryId'] == $categoryId;
        });

        return $this->buildItemSpecificArrayForParent($itemSpecifics, 0);
    }

    protected function buildItemSpecificArrayForParent(array $itemSpecifics, int $parentId): array
    {
        $result = [];
        foreach ($itemSpecifics as $itemSpecific) {
            if ($itemSpecific['parentId'] !== $parentId) {
                continue;
            }
            if (isset($result[$itemSpecific['name']])) {
                $result[$itemSpecific['name']] = (array) $result[$itemSpecific['name']];
                $result[$itemSpecific['name']][] = $itemSpecific['value'];
                continue;
            }
            if ($itemSpecific['value'] !== null) {
                $result[$itemSpecific['name']] = $itemSpecific['value'];
                continue;
            }
            $result[$itemSpecific['name']] = $this->buildItemSpecificArrayForParent($itemSpecifics, $itemSpecific['id']);
        }
        return $result;
    }


    protected function attachValidValuesToResults(array $results, ResultInterface $validValues): array
    {
        $validValues = ArrayUtils::iteratorToArray($validValues);
        foreach ($validValues as $validValue) {
            $productId = $validValue['productId'];
            $categoryId = $validValue['categoryId'];
            if (!isset($results[$productId])) {
                $results[$productId] = [$categoryId => []];
            }
            if (isset($results[$productId][$categoryId]['validValues'])) {
                continue;
            }
            $results[$productId][$categoryId]['validValues'] = $this->buildValidValuesArrayForProductAndCategory(
                $validValues,
                $productId,
                $categoryId
            );
        }
        return $results;
    }

    protected function buildValidValuesArrayForProductAndCategory(
        array $validValues,
        int $productId,
        int $categoryId
    ): array {
        $validValues = $itemSpecifics = array_filter($validValues, function($validValue) use ($productId, $categoryId) {
            return $validValue['productId'] == $productId && $validValue['categoryId'] == $categoryId;
        });

        $result = [];
        foreach ($validValues as $validValue) {
            $sku = $validValue['sku'];
            if (!isset($result[$sku])) {
                $result[$sku] = [];
            }

            $result[$sku][] = [
                'name' => $validValue['name'] ?? '',
                'option' => $validValue['option'] ?? '',
                'displayName' => $validValue['displayName'] ?? ''
            ];
        }
        return $result;
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
        $validValues = $array['validValues'] ?? [];
        unset($array['itemSpecifics'], $array['validValues']);

        $insert = $this->getInsert()->values(array_merge(
            ['productId' => $productId, 'categoryId' => $categoryId],
            $array
        ));
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();

        $this->insertItemSpecifics($itemSpecifics, $productId, $categoryId);
        $this->insertValidValues($validValues, $productId, $categoryId);
    }

    protected function insertItemSpecifics(
        array $itemSpecifics,
        int $productId,
        int $categoryId,
        int $parentId = 0
    ): void {
        foreach ($itemSpecifics as $name => $values) {
            $itemSpecificId = $this->insertItemSpecificName($name, $productId, $categoryId, $parentId);
            if (is_array($values) && isArrayAssociative($values)) {
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

    protected function insertValidValues(array $validValues, int $productId, int $categoryId): void
    {
        foreach ($validValues as $sku => $skuValidValues) {
            foreach ($skuValidValues as $validValue) {
                $insert = $this->getInsert('productCategoryAmazonValidValues')->values([
                    'productId' => $productId,
                    'categoryId' => $categoryId,
                    'sku' => $sku,
                    'name' => $validValue['name'],
                    'option' => $validValue['option'],
                    'displayName' => $validValue['displayName'],
                ]);
                $this->writeSql->prepareStatementForSqlObject($insert)->execute();
            }
        }
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

    protected function getValidValuesSelect(): Select
    {
        return $this->readSql
            ->select('productCategoryAmazonValidValues')
            ->columns(['productId', 'categoryId', 'sku', 'name', 'option', 'displayName']);
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