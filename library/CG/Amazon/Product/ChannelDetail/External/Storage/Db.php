<?php
namespace CG\Amazon\Product\ChannelDetail\External\Storage;

use CG\Amazon\Product\ChannelDetail\External;
use CG\Amazon\Product\ChannelDetail\External\StorageInterface;
use CG\Product\ChannelDetail\ExternalInterface;
use CG\Stdlib\Storage\Db\DeadlockHandlerTrait;
use CG\Stdlib\Storage\Db\Zend\TransactionTrait;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

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

    public function fetch(int $productId): ExternalInterface
    {
        $select = $this->getSelect()->where(['productAmazonDetail.productId' => $productId]);
        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );
        return External::fromArray($array[$productId] ?? []);
    }

    public function fetchMultiple(array $productIds): array
    {
        $select = $this->getSelect()->where(['productAmazonDetail.productId' => $productIds]);
        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );

        $externals = [];
        foreach ($productIds as $productId) {
            $externals[$productId] = External::fromArray($array[$productId] ?? []);
        }
        return $externals;
    }

    protected function mapResultsToArray(ResultInterface $results): array
    {
        $array = [];
        foreach ($results as $result) {
            $productId = $result['productId'];
            unset($result['productId']);
            $array[$productId] = $result;
        }
        return $array;
    }

    public function save(int $productId, ExternalInterface $external): void
    {
        $this->startTransactionAndHandleDeadlock([$this, 'saveExternalData'], func_get_args());
    }

    protected function saveExternalData(int $productId, ExternalInterface $external): void
    {
        $this->remove($productId);

        $insert = $this->getInsert()->values(array_merge(
            ['productId' => $productId],
            $external->toArray()
        ));

        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
    }

    public function remove(int $productId): void
    {
        $delete = $this->getDelete()->where(['productAmazonDetail.productId' => $productId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect(): Select
    {
        return $this->readSql->select('productAmazonDetail');
    }

    protected function getInsert(string $table = 'productAmazonDetail'): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete(): Delete
    {
        return $this->writeSql->delete('productAmazonDetail');
    }

    // Required by traits
    protected function getWriteSql()
    {
        return $this->writeSql;
    }
}