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
        $select = $this->getSelect()->where([
            'productCategoryAmazonDetail.productId' => $productId,
            'productCategoryAmazonDetail.categoryId' => $categoryId,
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
                    ->equalTo('productCategoryAmazonDetail.productId', $productId)
                    ->equalTo('productCategoryAmazonDetail.categoryId', $categoryId)
            );
        }

        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );
        $array = $this->filterResultsArrayToUniqueValues($array);

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
                $array[$productId][$categoryId] = array_merge($result, [
                    'itemSpecifics' => [],
                    'validValues' => [],
                ]);
            }
            $array = $this->mapItemSpecificFromResultToArray($result, $array, $productId, $categoryId);
            $array = $this->mapValidValueFromResultToArray($result, $array, $productId, $categoryId);
        }
        return $array;
    }

    protected function mapItemSpecificFromResultToArray(array $result, array $array, int $productId, int $categoryId): array
    {
        if (!isset($result['aisName'])) {
            return $array;
        }
        $itemSpecific = $result['aisName'];
        $itemSpecificValue = $result['aisValue'];
        $itemSpecifics = &$array[$productId][$categoryId]['itemSpecifics'];

        if ($this->itemSpecificHasMultipleUniqueValues($itemSpecifics, $itemSpecific, $itemSpecificValue)) {
            $itemSpecifics[$itemSpecific] = (array)$itemSpecifics[$itemSpecific];
            $itemSpecifics[$itemSpecific][] = $itemSpecificValue;
        } else {
            $itemSpecifics[$itemSpecific] = $itemSpecificValue;
        }
        
        return $array;
    }

    protected function itemSpecificHasMultipleUniqueValues(array $itemSpecifics, string $itemSpecific, string $latestValue): bool
    {
        return (isset($itemSpecifics[$itemSpecific]) &&
            (is_array($itemSpecifics[$itemSpecific]) || $itemSpecifics[$itemSpecific] !== $latestValue));
    }

    protected function mapValidValueFromResultToArray(array $result, array $array, int $productId, int $categoryId): array
    {
        if (!isset($result['avvName'])) {
            return $array;
        }
        $array[$productId][$categoryId]['validValues'][] = [
            'name' => $result['avvName'],
            'option' => $result['avvOption'],
            'displayName' => $result['avvDisplayName'],
        ];
        return $array;
    }

    protected function filterResultsArrayToUniqueValues(array $array): array
    {
        // Because we join on to multiple tables we end up with duplicated data
        // Filtering them out in PHP is more efficient then trying to do it in MySQL
        foreach ($array as $productId => &$categories) {
            foreach ($categories as $categoryId => &$data) {
                $data['itemSpecifics'] = $this->filterToUniqueItemSpecifics($data['itemSpecifics']);
                $data['validValues'] = $this->filterToUniqueValidValues($data['validValues']);
            }
        }
        return $array;
    }

    protected function filterToUniqueItemSpecifics(array $itemSpecifics): array
    {
        foreach ($itemSpecifics as $itemSpecific => &$itemSpecificValue) {
            if (is_array($itemSpecificValue)) {
                $itemSpecificValue = array_unique($itemSpecificValue);
            }
        }
        return $itemSpecifics;
    }

    protected function filterToUniqueValidValues(array $validValues): array
    {
        $validValuesSeen = [];
        $uniqueValidValues = array_filter($validValues, function($validValue) use (&$validValuesSeen)
        {
            if (!isset($validValuesSeen[$validValue['name']])) {
                $validValuesSeen[$validValue['name']] = true;
                return true;
            }
            return false;
        });
        // array_filter() always returns an associative array which causes problems when converted to JSON
        return array_values($uniqueValidValues);
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

        $this->saveItemSpecifics($itemSpecifics, $productId, $categoryId);
        $this->saveValidValues($validValues, $productId, $categoryId);
    }

    protected function saveItemSpecifics(array $itemSpecifics, int $productId, int $categoryId): void
    {
        foreach ($itemSpecifics as $name => $values) {
            foreach (((array) $values) as $value) {
                $insert = $this->getInsert('productCategoryAmazonItemSpecifics')->values([
                    'productId' => $productId,
                    'categoryId' => $categoryId,
                    'name' => $name,
                    'value' => $value,
                ]);
                $this->writeSql->prepareStatementForSqlObject($insert)->execute();
            }
        }
    }

    protected function saveValidValues(array $validValues, int $productId, int $categoryId): void
    {
        foreach ($validValues as $validValue) {
            $insert = $this->getInsert('productCategoryAmazonValidValues')->values([
                'productId' => $productId,
                'categoryId' => $categoryId,
                'name' => $validValue['name'],
                'option' => $validValue['option'],
                'displayName' => $validValue['displayName'],
            ]);
            $this->writeSql->prepareStatementForSqlObject($insert)->execute();
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

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select('productCategoryAmazonDetail')
            ->join(
                'productCategoryAmazonItemSpecifics',
                'productCategoryAmazonDetail.productId = productCategoryAmazonItemSpecifics.productId'
                . ' AND productCategoryAmazonDetail.categoryId = productCategoryAmazonItemSpecifics.categoryId',
                ['aisName' => 'name', 'aisValue' => 'value'],
                Select::JOIN_LEFT
            )
            ->join(
                'productCategoryAmazonValidValues',
                'productCategoryAmazonDetail.productId = productCategoryAmazonValidValues.productId'
                . ' AND productCategoryAmazonDetail.categoryId = productCategoryAmazonValidValues.categoryId',
                ['avvName' => 'name', 'avvOption' => 'option', 'avvDisplayName' => 'displayName'],
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