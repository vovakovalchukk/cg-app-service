<?php
namespace CG\Ebay\Product\ChannelDetail\External\Storage;

use CG\Ebay\Product\ChannelDetail\External;
use CG\Ebay\Product\ChannelDetail\External\StorageInterface;
use CG\Product\ChannelDetail\ExternalInterface;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;

class Db implements StorageInterface
{
    /** @var Sql */
    protected $readSql;
    /** @var Sql */
    protected $writeSql;

    public function __construct(
        Sql $readSql,
        Sql $writeSql
    ) {
        $this->readSql = $readSql;
        $this->writeSql = $writeSql;
    }

    public function fetch(int $productId): ExternalInterface
    {
        $select = $this->getSelect()->where(['productEbayDetail.productId' => $productId]);
        $array = $this->mapResultsToArray(
            $this->readSql->prepareStatementForSqlObject($select)->execute()
        );
        return External::fromArray($array[$productId] ?? []);
    }

    public function fetchMultiple(array $productIds): array
    {
        $select = $this->getSelect()->where(['productEbayDetail.productId' => $productIds]);
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
            if (!isset($array[$productId])) {
                $array[$productId] = [
                    'imageAttributeName' => $result['imageAttributeName'],
                    'attributeImageMap' => [],
                    'dispatchTimeMax' => $result['dispatchTimeMax'],
                    'shippingMethod' => $result['shippingMethod'],
                    'shippingPrice' => $result['shippingPrice'],
                    'variationToEpid' => json_decode($result['variationToEpid'], true),
                    'marketplace' => $result['marketplace']
                ];
            }
            if (isset($result['attributeValue'])) {
                $array[$productId]['attributeImageMap'][$result['attributeValue']] = $result['imageId'];
            }
        }
        return $array;
    }

    public function save(int $productId, ExternalInterface $external): void
    {
        $this->remove($productId);

        $array = $external->toArray();
        $attributeImageMap = $array['attributeImageMap'] ?? [];
        unset($array['attributeImageMap'], $array['variationToEpid'], $array['marketplace']);

        $insert = $this->getInsert()->values(array_merge(
            ['productId' => $productId],
            $array
        ));
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();

        foreach ($attributeImageMap as $attributeValue => $imageId) {
            $insert = $this->getInsert('productEbayAttributeImage')->values([
                'productId' => $productId,
                'attributeValue' => $attributeValue,
                'imageId' => $imageId,
            ]);
            $this->writeSql->prepareStatementForSqlObject($insert)->execute();
        }

        $this->saveEpid($productId, $external);
    }

    protected function saveEpid(int $productId, External $external): void
    {
        if (empty($external->getVariationToEpid()) || $external->getMarketplace() === null) {
            return;
        }

        $insert = $this->getInsert('productEbayEpid')->values([
            'productId' => $productId,
            'marketplace' => $external->getMarketplace(),
            'variationToEpid' => json_encode($external->getVariationToEpid())
        ]);
        $this->writeSql->prepareStatementForSqlObject($insert)->execute();
    }

    public function remove(int $productId): void
    {
        $delete = $this->getDelete()->where(['productEbayDetail.productId' => $productId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();

        $delete = $this->getDelete('productEbayEpid')->where(['productEbayEpid.productId' => $productId]);
        $this->writeSql->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getSelect(): Select
    {
        return $this->readSql
            ->select('productEbayDetail')
            ->join(
                'productEbayAttributeImage',
                'productEbayDetail.productId = productEbayAttributeImage.productId',
                ['attributeValue', 'imageId'],
                Select::JOIN_LEFT
            )
            ->join(
                'productEbayEpid',
                'productEbayEpid.productId = productEbayDetail.productId',
                ['variationToEpid', 'marketplace'],
                Select::JOIN_LEFT
            );
    }

    protected function getInsert(string $table = 'productEbayDetail'): Insert
    {
        return $this->writeSql->insert($table);
    }

    protected function getDelete(string $table = 'productEbayDetail'): Delete
    {
        return $this->writeSql->delete($table);
    }
}