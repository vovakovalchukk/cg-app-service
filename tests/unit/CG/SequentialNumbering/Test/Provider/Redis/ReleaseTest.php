<?php
namespace CG\SequentialNumbering\Test\Provider\Redis;

use CG\SequentialNumbering\Provider\Redis\Async\Release;
use CG\SequentialNumbering\Test\Provider\RedisTestTrait;
use PHPUnit_Framework_TestCase;
use Predis\Async\Client as PredisAsync;
use UnexpectedValueException;

/**
 * @backupGlobals disabled
 */
class ReleaseTest extends PHPUnit_Framework_TestCase
{
    use RedisTestTrait {
        setUpBeforeClass as setupPredis;
    }

    const KEY = 'GetNextAndLockTestKey';
    const QUEUE = 'WaitInQueueTestQueue';
    const LOCK = 'GetNextAndLockTestLock';
    const SEQUENCE_NAME = 'GetNextAndLockTestSequence';

    /**
     * @var Release $release
     */
    protected static $release;

    public static function setUpBeforeClass()
    {
        static::setupPredis();
        static::$release = new Release(
            static::QUEUE,
            static::LOCK,
            static::SEQUENCE_NAME
        );
    }

    /**
     * @afterClass
     */
    public static function reset()
    {
        static::$predis->del(static::KEY);
        static::$predis->del(static::QUEUE);
        static::$predis->del(static::LOCK);
    }

    public function setup()
    {
        static::reset();
    }

    /**
     * @group integration
     * @group markUsed
     */
    public function testReleaseWithLock()
    {
        static::$predis->set(static::KEY, 1);
        static::$predis->set(static::LOCK, 2);
        $this->release(2);
        $this->assertEquals(
            1,
            static::$predis->get(static::KEY),
            'Sequence number was changed even though we released it'
        );
    }

    /**
     * @group integration
     * @group markUsed
     */
    public function testMarkedUsedWithoutLock()
    {
        static::$predis->set(static::KEY, 1);
        static::$predis->set(static::LOCK, 1);
        try {
            $this->release(2);
            $this->fail('Should have thrown an exception if I mark used when don\t have the lock');
        } catch (UnexpectedValueException $exception) {
            $this->assertEquals(
                1,
                static::$predis->get(static::KEY),
                'Sequence number was changed even though we never had the lock to released'
            );
        }
    }

    protected function release($number)
    {
        $release = static::$release;
        try {
            $release(static::$predisAsync, $number);
        } finally {
            static::$predisAsync->disconnect();
        }
    }
} 
