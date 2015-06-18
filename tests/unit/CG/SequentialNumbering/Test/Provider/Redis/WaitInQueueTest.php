<?php
namespace CG\SequentialNumbering\Test\Provider\Redis;

use CG\SequentialNumbering\Provider\Redis\Async\WaitInQueue;
use CG\SequentialNumbering\Test\Provider\RedisTestTrait;
use PHPUnit_Framework_TestCase;
use Predis\Async\Client as PredisAsync;

/**
 * @backupGlobals disabled
 */
class WaitInQueueTest extends PHPUnit_Framework_TestCase
{
    use RedisTestTrait {
        setUpBeforeClass as setupPredis;
    }

    const ID = 'WaitInQueueTest';
    const QUEUE = 'WaitInQueueTestQueue';
    const LOCK = 'WaitInQueueTestLock';
    const SEQUENCE_NAME = 'WaitInQueueTestSequence';
    const TIMEOUT = 1;

    /**
     * @var WaitInQueue $waitInQueue
     */
    protected static $waitInQueue;

    public static function setUpBeforeClass()
    {
        static::setupPredis();
        static::$waitInQueue = new WaitInQueue(
            static::ID,
            static::QUEUE,
            static::LOCK,
            static::SEQUENCE_NAME,
            static::TIMEOUT
        );
    }

    /**
     * @afterClass
     */
    public static function reset()
    {
        static::$predis->del(static::ID);
        static::$predis->del(static::QUEUE);
        static::$predis->del(static::LOCK);
    }

    public function setup()
    {
        static::reset();
    }

    /**
     * @group integration
     * @group listener
     */
    public function testListenerHandlesOK()
    {
        $eventLoop = static::$predisAsync->getEventLoop();
        $timer = $eventLoop->addTimer(
            static::TIMEOUT,
            function() use($eventLoop) {
                $eventLoop->stop();
                $this->fail('Listener did not process event inside allowed timeout');
            }
        );

        $listener = clone static::$predisAsync;
        $listener->connect([static::$waitInQueue, 'listen']);

        $worker = clone static::$predisAsync;
        $worker->connect(
            function(PredisAsync $predisAsync) {
                $predisAsync->publish(
                    static::ID,
                    WaitInQueue::PUB_MSG_OK,
                    function($listeners) {
                        $this->assertGreaterThanOrEqual(1, $listeners, 'Expected listener to recieve published message');
                    }
                );
            }
        );

        try {
            $eventLoop->run();
        } finally {
            $eventLoop->cancelTimer($timer);
            $listener->disconnect();
            $worker->disconnect();
            unset($listener, $worker);
        }
    }

    /**
     * @group integration
     * @group wait
     */
    public function testWait()
    {
        $eventLoop = static::$predisAsync->getEventLoop();
        $waitInQueue = static::$waitInQueue;
        try {
            $waitInQueue(static::$predisAsync);
        } finally {
            static::$predisAsync->disconnect();
        }
    }

    /**
     * @group integration
     * @group wait
     * @expectedException \CG\SequentialNumbering\LockException
     */
    public function testWaitTimout()
    {
        static::$predis->set(static::LOCK, 1);
        $this->testWait();
    }
}
