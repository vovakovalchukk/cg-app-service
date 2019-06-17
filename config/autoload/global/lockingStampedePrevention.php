<?php

use CG\Cache\StampedePrevention\Locking as LockingStampedePrevention;

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
            'preferences' => array(
                LockingStampedePrevention::class => 'LockingStampedePreventionForAllSalesAccounts',
            )
        ]
    ]
];