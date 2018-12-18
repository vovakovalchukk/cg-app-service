<?php

namespace CG\Test\Stock\Location;

use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Runtime\PreconditionFailed;
use CG\Stock\Auditor;
use CG\Stock\Location\Entity as StockLocation;
use CG\Stock\Location\Mapper;
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Storage\Db as LocationStorage;
use CG\Stock\Storage\Api as StockStorage;
use CG\Zend\Stdlib\Db\Adapter\Driver\Mysqli\Mysqli;
use PHPUnit\Framework\TestCase;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

/**
 * Class Test
 * @package CG\Test\Stock\Location
 * @group integration
 */
class ServiceTest extends TestCase
{
    const HOST = 'mysql';
    const ROOT = ['username' => 'root', 'password' => 'password'];
    const USER = ['username' => 'phpunit', 'password' => 'phpunit'];
    const DB = 'cg_app';
    const CHARSET = 'utf8mb4';

    /**
     * @var LocationService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $locationService;

    /**
     * @var StockStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stockStorage;

    public static function setUpBeforeClass()
    {
        $connection = mysqli_init();
        while (!$connection->real_connect(static::HOST, static::ROOT['username'], static::ROOT['password'])) {
            sleep(5);
        }

        static::mysqli($connection, 'select_db', static::DB);
        static::mysqli($connection, 'set_charset', static::CHARSET);
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `stockLocation`');
        static::mysqli($connection, 'query', 'DROP TABLE IF EXISTS `stockTransaction`');
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `stockLocation` (
  `stockId` int(11) NOT NULL,
  `locationId` int(11) NOT NULL,
  `onHand` int(11) NOT NULL DEFAULT '0',
  `allocated` int(11) NOT NULL DEFAULT '0',
  `type` varchar(255) NOT NULL DEFAULT 'real',
  KEY `StockIdLocationId` (`stockId`,`locationId`),
  KEY `locationId` (`locationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );
        static::mysqli($connection, 'query',
            <<<SQL
CREATE TABLE `stockTransaction` (
  `id` varchar(255) NOT NULL,
  `appliedDate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL
        );

        $connection->close();
    }

    protected static function mysqli(\mysqli $connection, string $method, ...$args): void
    {
        if (!$connection->{$method}(...$args)) {
            throw new \RuntimeException($connection->error, $connection->errno);
        }
    }

    protected function setUp()
    {
        $sql = new Sql(
            new Adapter(
                new Mysqli([
                    'hostname' => static::HOST,
                    'username' => static::USER['username'],
                    'password' => static::USER['password'],
                    'database' => static::DB,
                    'charset' => static::CHARSET,
                ], null, null, ['buffer_results' => true])
            )
        );

        $locationMapper = new Mapper();

        $locationDbStorage = new LocationStorage($sql, $sql, $sql, $locationMapper);

        $auditor = $this->getMockBuilder(Auditor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStorage = $this->getMockBuilder(StockStorage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stockStorage->expects($this->any())
            ->method('fetch')
            ->willThrowException(new NotFound());

        $this->locationService = new LocationService(
            $locationDbStorage,
            $locationMapper,
            $auditor,
            $this->stockStorage
        );
    }

    public function testStockLocation()
    {
        $onHand = 10;
        $allocated = 5;

        $location = $this->createStockLocation(1, 1, $onHand, $allocated);
        $adjustmentIds = ['abc-def'];

        $test = $this->locationService->save($location, $adjustmentIds);
        $this->assertEquals($test->getData(), $location->toArray());

        $this->expectException(PreconditionFailed::class);

        try {
            $location->setAllocated(10);
            $location->setOnHand(20);
            $this->locationService->save($location, $adjustmentIds);
        } catch (PreconditionFailed $e) {
            /** @var StockLocation $fetchedLocation */
            $fetchedLocation =  $this->locationService->fetch('1-1');
            $this->assertTrue($fetchedLocation ->getOnHand() === $onHand);
            $this->assertTrue($fetchedLocation ->getAllocated() === $allocated);
            throw $e;
        }
    }

    protected function createStockLocation(
        int $stockId = 0,
        int $locationId = 0,
        int $onHand = 0,
        int $allocated = 0
    ): StockLocation {
        $stockId = $stockId === 0 ? mt_rand(1, 99999) : $stockId;

        return new StockLocation(
            $stockId,
            $locationId,
            $onHand,
            $allocated
        );
    }
}
