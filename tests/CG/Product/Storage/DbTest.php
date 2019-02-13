<?php
namespace CG\Test\Product\Storage;

use CG\Product\Collection;
use CG\Product\Entity as Product;
use CG\Product\Filter;
use CG\Product\Mapper;
use CG\Product\ServiceAbstract as Service;
use CG\Product\Storage\Db;
use CG\Stdlib\Exception\Runtime\NotFound;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\Mysqli\Mysqli;
use Zend\Db\Sql\Sql;

/**
 * @group integration
 */
class DbTest extends TestCase
{
    const HOST = 'mysql';
    const ROOT = ['username' => 'root', 'password' => 'password'];
    const USER = ['username' => 'phpunit', 'password' => 'phpunit'];
    const DB = 'cg_app';
    const CHARSET = 'utf8mb4';

    /** @var Service */
    protected $service;

    public static function setUpBeforeClass()
    {
        $connection = mysqli_init();
        while (!$connection->real_connect(static::HOST, static::ROOT['username'], static::ROOT['password'])) {
            sleep(5);
        }
        static::mysqli($connection, 'select_db', static::DB);
        static::mysqli($connection, 'set_charset', static::CHARSET);
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productPickingLocation`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productListingImage`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productImage`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productTaxRate`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productAttributeValue`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `productAttribute`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `product`');
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `organisationUnitId` int(11) NOT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `parentProductId` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `cgCreationDate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ParentProductId` (`parentProductId`),
  KEY `Deleted` (`deleted`),
  KEY `OrganisationUnitIdSku` (`organisationUnitId`,`sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productAttribute` (
  `id` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ProductIdName` (`productId`,`name`),
  CONSTRAINT `productAttribute_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productAttributeValue` (
  `productAttributeId` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `productId` int(11) NOT NULL,
  KEY `ProductIdProductAttributeId` (`productId`,`productAttributeId`),
  KEY `productAttributeId` (`productAttributeId`),
  CONSTRAINT `productAttributeValue_ibfk_1` FOREIGN KEY (`productAttributeId`) REFERENCES `productAttribute` (`id`) ON DELETE CASCADE,
  CONSTRAINT `productAttributeValue_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productTaxRate` (
  `productId` int(11) NOT NULL,
  `VATCountryCode` varchar(255) NOT NULL,
  `taxRateId` varchar(255) NOT NULL,
  UNIQUE KEY `ProductIdVATCountryCode` (`productId`,`VATCountryCode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productImage` (
  `productId` int(11) NOT NULL,
  `imageId` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`productId`,`imageId`),
  CONSTRAINT `productImage_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productListingImage` (
  `productId` int(11) NOT NULL,
  `listingId` int(11) NOT NULL,
  `imageId` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`productId`,`listingId`,`imageId`),
  KEY `_productListingImage_ibfk_2` (`listingId`),
  CONSTRAINT `productListingImage_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `productPickingLocation` (
  `productId` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `name` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`productId`,`level`),
  KEY `Level` (`level`),
  CONSTRAINT `productPickingLocation_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL
        );
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(1, 1, "simple", "simple", 0, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(2, 1, "", "parent-multi", 0, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(3, 1, "var1", "var1", 2, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(4, 1, "var2", "var3", 2, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(5, 1, "var3", "var3", 2, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(6, 1, "", "parent-single", 0, false, NOW())');
        static::mysqli($connection, 'query', 'INSERT INTO `product` VALUES(7, 1, "var4", "var4", 6, false, NOW())');
        $connection->close();
    }

    protected static function mysqli(\mysqli $connection, string $method, ...$args): void
    {
        if (!$connection->{$method}(...$args)) {
            throw new \RuntimeException($connection->error, $connection->errno);
        }
    }

    protected static function getClassMethods(string $class): array
    {
        return array_map(
            function(\ReflectionMethod $method) {
                return $method->getName();
            },
            (new \ReflectionClass($class))->getMethods()
        );
    }

    protected function setUp()
    {
        $this->service = $this
            ->getMockBuilder(Service::class)
            ->disableOriginalConstructor()
            ->setMethods(array_filter(static::getClassMethods(Service::class), function(string $method) {
                return !in_array($method, [
                    'fetch',
                    'fetchCollectionByFilter',
                    'setRepository',
                    'getRepository'
                ]);
            }))
            ->getMock();

        $this->service
            ->method('fetchCollectionEmbeds')
            ->willReturnCallback(function(Collection $collection) {
                try {
                    /** @var Collection $variations */
                    $variations = $this->service->getRepository()->fetchCollectionByFilter(
                        (new Filter('all', 1))->setParentProductId($collection->getIds())
                    );
                } catch (NotFound $exception) {
                    // no variations found not a problem continue on
                }

                /** @var Product $product */
                foreach ($collection as $product) {
                    if (isset($variations) && $variations instanceof Collection) {
                        $product->setVariations($variations->getByParentProductId($product->getId()));
                    }
                }

                return $collection;
            });

        $sql = new Sql(new Adapter(new Mysqli([
            'hostname' => static::HOST,
            'username' => static::USER['username'],
            'password' => static::USER['password'],
            'database' => static::DB,
            'charset' => static::CHARSET,
        ], null, null, ['buffer_results' => true])));

        $mapper = $this
            ->getMockBuilder(Mapper::class)
            ->disableOriginalConstructor()
            ->setMethods(array_filter(static::getClassMethods(Mapper::class), function(string $method) {
                return !in_array($method, [
                    'fromMysqlRows',
                    'productArrayFromRow',
                    'attachAttributeNamesToProductArray',
                    'attachAttributeValuesToProductArray',
                    'attachProductImageIdsToProductArray',
                    'attachProductTaxRatesToProductArray',
                    'attachProductPickingLocations',
                    'fromArraysToCollection',
                    'fromArrays',
                    'fromArray',
                    'getEntityClass'
                ]);
            }))
            ->getMock();

        $this->service->setRepository(new Db($sql, $sql, $sql, $mapper));
    }

    public function getExactSkuMatchSkus()
    {
        yield ['simple', 1];
        yield ['var1', 1];
        yield ['var2', 1];
        yield ['var3', 1];
        yield ['var4', 2];
    }

    /**
     * @dataProvider getExactSkuMatchSkus
     */
    public function testExactSkuMatch(string $sku, int $matches)
    {
        /** @var Collection $match */
        $match = $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku([$sku])
                ->setSkuMatchType([Filter::SKU_MATCH_ALL])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );

        $this->assertEquals($matches, $match->getTotal(), 'Db didn\'t return expected products');
        foreach ($match as $product) {
            if (!$product->isParent()) {
                $this->assertEquals($sku, $product->getSku(), 'Returned product has wrong sku');
                continue;
            }

            $variations = $product->getVariations();
            $this->assertEquals(1, $variations->count(), 'Returned parent product with more than expected variations');
            $this->assertEquals($sku, $variations->getFirst()->getSku(), 'Returned parent product has wrong variation sku');
        }
    }

    /**
     * @expectedException \CG\Stdlib\Exception\Runtime\NotFound
     */
    public function testExactSkuNotFound()
    {
        $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku(['unknown'])
                ->setSkuMatchType([Filter::SKU_MATCH_ALL])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );
    }

    public function getSubsetSkuMatchSkus()
    {
        yield [['var1', 'var2', 'var3', 'var4'], 6];
        yield [['var4', 'var5'], 2];
    }

    /**
     * @dataProvider getSubsetSkuMatchSkus
     */
    public function testSubsetSkuMatch(array $skus, int $matches)
    {
        /** @var Collection $match */
        $match = $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku($skus)
                ->setSkuMatchType([Filter::SKU_MATCH_SUBSET])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );

        $this->assertEquals($matches, $match->getTotal(), 'Db didn\'t return expected products');
        /** @var Product $product */
        foreach ($match as $product) {
            if ($product->isVariation()) {
                $this->assertEquals(true, in_array($product->getSku(), $skus), 'Returned variation doesn\'t have a valid sku');
                continue;
            }

            if ($product->isParent()) {
                $variationsSkus = array_map(function(string $sku) use($skus) {
                    return in_array($sku, $skus);
                }, $product->getVariations()->getArrayOf('sku'));
                $this->assertLessThan(count($skus), count($variationsSkus), 'Returned parent product doesn\'t have enough matching variations');
                $this->assertGreaterThanOrEqual(1, count(array_filter($variationsSkus)), 'Returned parent product doesn\'t have enough matching variations');
                continue;
            }

            $this->fail('Matched product isn\'t a parent or variation product so can\'t match a subset!');
        }
    }

    /**
     * @expectedException \CG\Stdlib\Exception\Runtime\NotFound
     */
    public function testSubsetSkuNotFound()
    {
        $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku(['unknown'])
                ->setSkuMatchType([Filter::SKU_MATCH_ALL])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );
    }

    public function getSupersetSkuMatchSkus()
    {
        yield [['var1']];
        yield [['var2']];
        yield [['var3']];
        yield [['var1', 'var2']];
        yield [['var2', 'var3']];
        yield [['var1', 'var3']];
    }

    /**
     * @dataProvider getSupersetSkuMatchSkus
     */
    public function testSupersetSkuMatch(array $skus)
    {
        /** @var Collection $match */
        $match = $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku($skus)
                ->setSkuMatchType([Filter::SKU_MATCH_SUPERSET])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );

        $this->assertEquals(1, $match->getTotal(), 'Db returned more products than expected');

        /** @var Product $product */
        $product = $match->getFirst();
        $this->assertEquals(true, $product->isParent(), 'Returned product isn\'t a parent product');

        $variationsSkus = array_map(function(string $sku) use($skus) {
            return in_array($sku, $skus);
        }, $product->getVariations()->getArrayOf('sku'));
        $this->assertGreaterThan(count($skus), count($variationsSkus), 'Returned parent product doesn\'t have enough matching variations');
        $this->assertEquals(count($skus), count(array_filter($variationsSkus)), 'Returned parent product doesn\'t have enough matching variations');
    }

    /**
     * @expectedException \CG\Stdlib\Exception\Runtime\NotFound
     */
    public function testSupersetSkuNotFound()
    {
        $this->service->fetchCollectionByFilter(
            (new Filter('all', 1))
                ->setSku(['var1', 'unknown'])
                ->setSkuMatchType([Filter::SKU_MATCH_ALL])
                ->setType([Filter::TYPE_SIMPLE, Filter::TYPE_PARENT, Filter::TYPE_VARIATION])
        );
    }
}
