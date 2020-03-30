<?php

use CG\Cache\StampedePrevention\Caching\Strategy\Runtime as RuntimeCachingStrategy;
use CG\Cache\StampedePrevention\Caching\StrategyInterface as CachingStrategyInterface;
use CG\Cache\StampedePrevention\Locking as LockingStampedePrevention;
use CG\Cache\StampedePrevention\Locking\Strategy\Standard as StandardLockingStrategy;
use CG\Cache\StampedePrevention\Locking\StrategyInterface as LockingStrategyInterface;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'LockingStampedePreventionForAllSalesAccounts' => LockingStampedePrevention::class,
            ],
            'LockingStampedePreventionForAllSalesAccounts' => [
                'parameters' => [
                    'redis' => 'unreliable_redis',
                    'dataKey' => 'accounts-sales',
                    'cacheTtl' => 3600,
                    'lockTtl' => 10,
                ]
            ],
            'preferences' => [
                LockingStampedePrevention::class => 'LockingStampedePreventionForAllSalesAccounts',
                LockingStrategyInterface::class => StandardLockingStrategy::class,
                CachingStrategyInterface::class => RuntimeCachingStrategy::class,
            ]
        ]
    ]
];