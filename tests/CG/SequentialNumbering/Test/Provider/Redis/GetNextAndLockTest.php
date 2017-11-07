<?php
namespace CG\SequentialNumbering\Test\Provider\Redis;

use PHPUnit_Framework_TestCase;
use CG\SequentialNumbering\Provider\Redis\Async\GetNextAndLock;
use CG\SequentialNumbering\Test\Provider\RedisTestTrait;

/**
 * @group integration
 * @backupGlobals disabled
 */
class GetNextAndLockTest extends PHPUnit_Framework_TestCase
{
    use RedisTestTrait {
        setUpBeforeClass as setupPredis;
    }

    const KEY = 'GetNextAndLockTestKey';
    const LOCK = 'GetNextAndLockTestLock';
    const SEQUENCE_NAME = 'GetNextAndLockTestSequence';

    /**
     * @var GetNextAndLock $getNextAndLock
     */
    protected static $getNextAndLock;

    public static function setUpBeforeClass()
    {
        static::setupPredis();
        static::$getNextAndLock = new GetNextAndLock(
            static::KEY,
            static::LOCK,
            static::SEQUENCE_NAME
        );
    }

    public function setup()
    {
        static::reset();
    }

    /**
     * @afterClass
     */
    public static function reset()
    {
        static::$predis->del(static::KEY);
        static::$predis->del(static::LOCK);
    }

    /**
     * @group getNext
     */
    public function testGetFirstNumber()
    {
        $this->testSequence(1);
    }

    /**
     * @group getNext
     */
    public function testGetNextNumber()
    {
        static::$predis->set(static::KEY, 1);
        $this->testSequence(2);
    }

    protected function testSequence($expected)
    {
        $getNextAndLock = static::$getNextAndLock;
        try {
            $next = $getNextAndLock(static::$predisAsync);
            $this->assertEquals($expected, $next, 'Sequence did not provide expected number');

            $lock = static::$predis->get(static::LOCK);
            $this->assertEquals($next, $lock, 'Failed to lock to the current value in the sequence');
        } finally {
            static::$predisAsync->disconnect();
        }
    }
}
