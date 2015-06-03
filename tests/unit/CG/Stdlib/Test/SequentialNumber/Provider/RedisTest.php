<?php
namespace CG\Stdlib\Test\SequentialNumber\Provider;

use CG\Stdlib\SequentialNumber\Provider\Redis;
use PHPUnit_Framework_TestCase;
use Predis\Client as Predis;

class RedisTest extends PHPUnit_Framework_TestCase
{
    const SEQUENCE_NAME = 'IntegrationTest';

    protected static $di;
    protected $provider;

    public static function setUpBeforeClass()
    {
        require_once 'application/bootstrap.php';
        static::$di = $di;
    }

    public function setUp()
    {
        $di = static::$di;
        $this->provider = $di->get(Redis::class);
        $sequenceName = static::SEQUENCE_NAME;
        $this->resetForIntegrationTests($this->provider, $sequenceName);
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testGetNextReturnsFirstNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName);
        $this->assertEquals(1, $result);
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testGetNextReturnsSequentialNumbers()
    {
        $sequenceName = static::SEQUENCE_NAME;
        
        $firstResult = $this->provider->getNext($sequenceName);
        $this->assertEquals(1, $firstResult);
        $this->provider->markUsed($sequenceName, $firstResult);

        $secondResult = $this->provider->getNext($sequenceName);
        $this->assertEquals(2, $secondResult);
        $this->provider->markUsed($sequenceName, $secondResult);

        $thirdResult = $this->provider->getNext($sequenceName);
        $this->assertEquals(3, $thirdResult);
        $this->provider->markUsed($sequenceName, $thirdResult);
    }

    protected function resetForIntegrationTests($provider, $sequenceName)
    {
        $di = static::$di;
        $predis = $di->get(Predis::class);
        $numberKey = $provider->generateNumberKey($sequenceName);
        $lockKey = $provider->generateLockKey($sequenceName);
        $predis->del($numberKey);
        $predis->del($lockKey);
    }
}
