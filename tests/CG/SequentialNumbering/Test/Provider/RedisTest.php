<?php
namespace CG\SequentialNumbering\Test\Provider;

use CG\SequentialNumbering\LockException;
use CG\SequentialNumbering\Provider\Redis;
use PHPUnit\Framework\TestCase;
use Spork\Fork;
use Spork\ProcessManager;
use UnexpectedValueException;

/**
 * @group integration
 * @backupGlobals disabled
 */
class RedisTest extends TestCase
{
    use RedisTestTrait {
        setUpBeforeClass as setupPredis;
    }

    const SEQUENCE_NAME = 'IntegrationTest';
    const TIMEOUT = 5;

    /**
     * @var Redis $provider
     */
    protected $provider;

    public function setUp()
    {
        $di = static::$di;
        $this->provider = $di->get(Redis::class);
        $sequenceName = static::SEQUENCE_NAME;
        $this->resetForIntegrationTests($this->provider, $sequenceName);
    }

    /**
     * @group onebyone
     */
    public function testGetNextReturnsFirstNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
    }

    /**
     * @group onebyone
     */
    public function testGetNextThrowsExceptionIfLockNotReleased()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->setExpectedException(LockException::class);
        $this->provider->getNext($sequenceName, static::TIMEOUT);
    }

    /**
     * @group onebyone
     */
    public function testMarkUsedSucceedsForCorrectNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->provider->markUsed($sequenceName, $result);
        // An exception would be thrown if there was a problem
        $this->assertTrue(true);
    }

    /**
     * @group onebyone
     */
    public function testMarkUsedThrowsExceptionForIncorrectNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->setExpectedException(\UnexpectedValueException::class);
        $this->provider->markUsed($sequenceName, $result+1);
    }

    /**
     * @group onebyone
     */
    public function testGetNextReturnsSequentialNumbersWhenEachIsUsed()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $max = 5;
        for ($count = 1; $count <= $max; $count++) {
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            $this->assertEquals($count, $result);
            $this->provider->markUsed($sequenceName, $result);
        }
    }

    /**
     * @group onebyone
     */
    public function testLockExceptionCanBeOvercomeWithMarkUsed()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        try {
            $this->provider->getNext($sequenceName, static::TIMEOUT);
            $this->fail('LockException should have been thrown');

        } catch (LockException $e) {
            $this->assertEquals(1, $e->getLockedNumber(), 'Locked number referenced in exception not as expected');
            $this->provider->markUsed($sequenceName, $e->getLockedNumber());
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            $this->assertEquals(2, $result, 'Second sequential number not 2');
        }
    }

    /**
     * @group onebyone
     */
    public function testReleaseResetsNextNumber()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        $this->provider->release($sequenceName, $result);
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'Second attempt at first sequential number not 1');
    }

    /**
     * @group onebyone
     */
    public function testLockExceptionCanBeOvercomeWithRelease()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');
        try {
            $this->provider->getNext($sequenceName, static::TIMEOUT);
            $this->assertTrue(false, 'LockException should have been thrown');

        } catch (LockException $e) {
            $this->assertEquals(1, $e->getLockedNumber(), 'Locked number referenced in exception not as expected');
            $this->provider->release($sequenceName, $e->getLockedNumber());
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            $this->assertEquals(1, $result, 'Second attempt at first sequential number not 1');
        }
    }

    /**
     * @group onebyone
     */
    public function testForceMarkUsedAfterCleanup()
    {
        $sequenceName = static::SEQUENCE_NAME;
        $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result, 'First sequential number not 1');

        $this->provider->release($sequenceName, $result);

        $retry = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $retry, 'First sequential number not 1');
        $this->provider->markUsed($sequenceName, $retry);

        $next = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(2, $next, 'Second sequential number not 2');
        $this->provider->markUsed($sequenceName, $next);

        try {
            $this->provider->markUsed($sequenceName, $result);
        } catch (UnexpectedValueException $e) {
            $this->assertEquals(1, $result, 'Locked number referenced in exception not as expected');
//            $this->provider->markPreviouslyUsed($sequenceName, $result);
        }

        $numberKey = $this->provider->generateNumberKey($sequenceName);
        $this->assertEquals($next, static::$predis->get($numberKey), 'Should still have the last sequence number');
    }

    /**
     * @group queued
     */
    public function testMultipleGetNextRequestsAreQueuedAndGetSequentialNumbers()
    {
        $di = static::$di;
        $processManager = $di->get(ProcessManager::class);
        $predis = $di->get('reliable_redis');
        $sequenceName = static::SEQUENCE_NAME;

        $result1 = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result1, 'First sequential number not 1');

        // Need to call getNext() again but without blocking so we can then call markUsed() on the first one. Fork.
        $fork1 = $this->forkToGetNextAndMarkUsed($processManager, $predis, $sequenceName);
        $fork1->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('2: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(2, $result, 'Second sequential number not 2' . ' [' . $fork->getPid() . ']');
        });

        $fork2 = $this->forkToGetNextAndMarkUsed($processManager, $predis, $sequenceName);
        $fork2->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('3: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(3, $result, 'Third sequential number not 3' . ' [' . $fork->getPid() . ']');
        });

        // Make the first child proc wait for a short time but less than the timeout
        sleep(1);
        $this->provider->markUsed($sequenceName, $result1);
        $fork1->wait();
        $fork2->wait();
    }

    /**
     * @group queued
     */
    public function testMultipleGetNextRequestsWorkWithRelease()
    {
        $di = static::$di;
        $processManager = $di->get(ProcessManager::class);
        $predis = $di->get('reliable_redis');
        $sequenceName = static::SEQUENCE_NAME;

        $result1 = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result1, 'First sequential number not 1');

        // Need to call getNext() again but without blocking so we can then call markUsed() on the first one. Fork.
        $fork1 = $this->forkToGetNextAndRelease($processManager, $predis, $sequenceName);
        $fork1->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('2: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(2, $result, 'Second sequential number not 2' . ' [' . $fork->getPid() . ']');
        });

        $fork2 = $this->forkToGetNextAndMarkUsed($processManager, $predis, $sequenceName);
        $fork2->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('3: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(2, $result, 'Third request (after a release) for sequential number not 2' . ' [' . $fork->getPid() . ']');
        });

        // Make the first child proc wait for a short time but less than the timeout
        sleep(1);
        $this->provider->markUsed($sequenceName, $result1);
        $fork1->wait();
        $fork2->wait();
    }

    /**
     * @group queued
     */
    public function testQueuedItemTimeoutWorks() 
    {
        $di = static::$di;
        $processManager = $di->get(ProcessManager::class);
        $predis = $di->get('reliable_redis');
        $sequenceName = static::SEQUENCE_NAME;

        $result1 = $this->provider->getNext($sequenceName, static::TIMEOUT);
        $this->assertEquals(1, $result1, 'First sequential number not 1');

        $fork1 = $this->forkToGetNextWithoutRelease($processManager, $predis, $sequenceName);
        $fork1->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('2: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(2, $result, 'Second sequential number not 2' . ' [' . $fork->getPid() . ']');
        });

        $fork2 = $this->forkToGetNextCatchLock($processManager, $predis, $sequenceName, 0.5);
        $fork2->always(function(Fork $fork)
        {
            $error = $fork->getError();
            if ($error) {
                $this->fail('3: ' . $error->getMessage() . ' [' . $fork->getPid() . ']');
            }
            $result = $fork->getResult();
            $this->assertEquals(3, $result, 'Third sequential number not 3' . ' [' . $fork->getPid() . ']');
        });

        // Make the first child proc wait for a short time but less than the timeout
        sleep(1);
        $this->provider->markUsed($sequenceName, $result1);
        $fork1->wait();
        $fork2->wait();
    }

    protected function forkToGetNextAndMarkUsed($processManager, $predis, $sequenceName)
    {
        $fork = $processManager->fork(function() use ($sequenceName, $predis)
        {
            // Can't share a Redis connection with the parent as we'll get the "cant pub and sub on same channel" problem
            // Not an issue outside of the tests because each PHP thread would only be requesting one number at a time
            $predis->disconnect();
            $predis->connect();
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            // Make the next proc wait
            sleep(1);
            $this->provider->markUsed($sequenceName, $result);
            return $result;
        });
        return $fork;
    }

    protected function forkToGetNextAndRelease($processManager, $predis, $sequenceName)
    {
        $fork = $processManager->fork(function() use ($sequenceName, $predis)
        {
            // Can't share a Redis connection with the parent as we'll get the "cant pub and sub on same channel" problem
            // Not an issue outside of the tests because each PHP thread would only be requesting one number at a time
            $predis->disconnect();
            $predis->connect();
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            // Make the next proc wait
            sleep(1);
            $this->provider->release($sequenceName, $result);
            return $result;
        });
        return $fork;
    }

    protected function forkToGetNextWithoutRelease($processManager, $predis, $sequenceName, $sleep = 0)
    {
        $fork = $processManager->fork(function() use ($sequenceName, $predis, $sleep)
        {
            usleep($sleep * 1000000);

            // Can't share a Redis connection with the parent as we'll get the "cant pub and sub on same channel" problem
            // Not an issue outside of the tests because each PHP thread would only be requesting one number at a time
            $predis->disconnect();
            $predis->connect();
            $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
            // Deliberately NOT marking as used or releasing
            return $result;
        });
        return $fork;
    }

    protected function forkToGetNextCatchLock($processManager, $predis, $sequenceName, $sleep = 0)
    {
        $fork = $processManager->fork(function() use ($sequenceName, $predis, $sleep)
        {
            usleep($sleep * 1000000);

            // Can't share a Redis connection with the parent as we'll get the "cant pub and sub on same channel" problem
            // Not an issue outside of the tests because each PHP thread would only be requesting one number at a time
            $predis->disconnect();
            $predis->connect();
            try {
                 $this->provider->getNext($sequenceName, static::TIMEOUT);
                 // Shouldnt get here
                 return 0;
                 
            } catch (LockException $e) {
                $this->provider->markUsed($sequenceName, $e->getLockedNumber());
                $result = $this->provider->getNext($sequenceName, static::TIMEOUT);
                return $result;
            }
        });
        return $fork;
    }

    protected function resetForIntegrationTests(Redis $provider, $sequenceName)
    {
        $predis = static::$predis;
        $numberKey = $provider->generateNumberKey($sequenceName);
        $lockKey = $provider->generateLockKey($sequenceName);
        $queueKey = $provider->generateQueueKey($sequenceName);
        $predis->del($numberKey);
        $predis->del($lockKey);
        $predis->del($queueKey);
    }
}
