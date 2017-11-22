<?php
namespace CG\Test\SequentialNumbering\Provider\Redis;

use CG\SequentialNumbering\Provider\Redis\Async\MarkUsed;
use CG\Test\SequentialNumbering\Provider\RedisTestTrait;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * @group integration
 * @backupGlobals disabled
 */
class MarkUsedTest extends TestCase
{
    use RedisTestTrait {
        setUpBeforeClass as setupPredis;
    }

    const KEY = 'GetNextAndLockTestKey';
    const QUEUE = 'WaitInQueueTestQueue';
    const LOCK = 'GetNextAndLockTestLock';
    const SEQUENCE_NAME = 'GetNextAndLockTestSequence';

    /**
     * @var MarkUsed $markUsed
     */
    protected static $markUsed;

    public static function setUpBeforeClass()
    {
        static::setupPredis();
        static::$markUsed = new MarkUsed(
            static::KEY,
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
     * @group markUsed
     */
    public function testMarkedUsedWithLock()
    {
        static::$predis->set(static::LOCK, 1);
        $this->markUsed(1);
        $this->assertEquals(
            1,
            static::$predis->get(static::KEY),
            'Number was not saved as current number in sequence'
        );
    }

    /**
     * @group markUsed
     */
    public function testMarkedUsedWithoutLock()
    {
        static::$predis->set(static::KEY, 1);
        static::$predis->set(static::LOCK, 1);
        try {
            $this->markUsed(2);
            $this->fail('Should have thrown an exception if I mark used when don\t have the lock');
        } catch (UnexpectedValueException $exception) {
            $this->assertEquals(
                1,
                static::$predis->get(static::KEY),
                'Number was saved as current number in sequence even though we didn\'t have the lock'
            );
        }
    }

    protected function markUsed($number)
    {
        $markUsed = static::$markUsed;
        try {
            $markUsed(static::$predisAsync, $number);
        } finally {
            static::$predisAsync->disconnect();
        }
    }
} 
