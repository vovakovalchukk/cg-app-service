<?php
namespace CG\SequentialNumbering\Test\Provider;

use CG\SequentialNumbering\Provider\Redis;
use CG\SequentialNumbering\LockException;
use PHPUnit_Framework_TestCase;
use Predis\Client as Predis;

class RedisTest extends PHPUnit_Framework_TestCase
{
    const SEQUENCE_NAME = 'IntegrationTest';
    const TIMEOUT_MCS = 3000000; //3sec

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
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testGetNextThrowsExceptionIfLockNotReleased()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->setExpectedException(LockException::class);
        $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testMarkUsedSucceedsForCorrectNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->provider->markUsed($sequenceName, $result);
        // An exception would be thrown if there was a problem
        $this->assertTrue(true);
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testMarkUsedThrowsExceptionForIncorrectNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->setExpectedException(\UnexpectedValueException::class);
        $this->provider->markUsed($sequenceName, $result+1);
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testGetNextReturnsSequentialNumbersWhenEachIsUsed()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $max = 5;
        for ($count = 1; $count <= $max; $count++) {
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
            $this->assertEquals($count, $result);
            $this->provider->markUsed($sequenceName, $result);
        }
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testLockExceptionCanBeOvercomeWithMarkUsed()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        try {
            $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
            $this->assertTrue(false, 'LockException should have been thrown');

        } catch (LockException $e) {
            $this->assertEquals(1, $e->getLockedNumber(), 'Locked number referenced in exception not as expected');
            $this->provider->markUsed($sequenceName, $e->getLockedNumber());
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
            $this->assertEquals(2, $result, 'Second sequential number not 2');
        }
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testReleaseResetsNextNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->provider->release($sequenceName, $result);
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'Second attempt at first sequential number not 1');
    }

    /**
     * @group integration
     * @backupGlobals disabled
     */
    public function testLockExceptionCanBeOvercomeWithRelease()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        try {
            $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
            $this->assertTrue(false, 'LockException should have been thrown');

        } catch (LockException $e) {
            $this->assertEquals(1, $e->getLockedNumber(), 'Locked number referenced in exception not as expected');
            $this->provider->release($sequenceName, $e->getLockedNumber());
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT_MCS);
            $this->assertEquals(1, $result, 'Second attempt at first sequential number not 1');
        }
    }

    protected function resetForIntegrationTests($provider, $sequenceName)
    {
        $di = static::$di;
        $predis = $di->get('reliable_redis');
        $numberKey = $provider->generateNumberKey($sequenceName);
        $lockKey = $provider->generateLockKey($sequenceName);
        $queueKey = $provider->generateQueueKey($sequenceName);
        $predis->del($numberKey);
        $predis->del($lockKey);
        $predis->del($queueKey);
    }
}
