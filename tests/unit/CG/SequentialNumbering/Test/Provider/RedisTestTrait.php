<?php
namespace CG\SequentialNumbering\Test\Provider;

use CG\SequentialNumbering\Provider\Redis\GetNextAndLockCommand;
use CG\SequentialNumbering\Provider\Redis\SetCurrentThenUnlockAndNotifyNextCommand;
use CG\SequentialNumbering\Provider\Redis\UnlockAndNotifyNextCommand;
use Predis\Async\Client as PredisAsync;
use Predis\Client as Predis;
use Zend\Di\Di;

trait RedisTestTrait
{
    /**
     * @var Di $di
     */
    protected static $di;
    /**
     * @var Predis $predis
     */
    protected static $predis;
    /**
     * @var PredisAsync $predisAsync
     */
    protected static $predisAsync;

    public static function setUpBeforeClass()
    {
        static::$di = $GLOBALS['di'];
        static::$predis = static::$di->get('reliable_redis');
        static::$predisAsync = static::$di->get('reliable_redis_async');
        static::$predisAsync->getProfile()->defineCommand('getNextAndLock', GetNextAndLockCommand::class);
        static::$predisAsync->getProfile()->defineCommand('setCurrentThenUnlockAndNotifyNext', SetCurrentThenUnlockAndNotifyNextCommand::class);
        static::$predisAsync->getProfile()->defineCommand('unlockAndNotifyNext', UnlockAndNotifyNextCommand::class);
    }
} 
