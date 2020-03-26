<?php

use CG\CGLib\Adjustment\Queuer as StockAdjustmentQueuer;
use CG\Redis\Queue as RedisQueue;

return [
    'di' => [
        'instance' => [
            'alias' => [
                'StockAdjustmentRedisQueue' => RedisQueue::class,
            ],
            StockAdjustmentQueuer::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                    'queue' => 'StockAdjustmentRedisQueue',
                ]
            ],
            'StockAdjustmentRedisQueue' => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                    'keyPrefix' => StockAdjustmentQueuer::QUEUE_KEY_PREFIX,
                ]
            ]
        ]
    ]
];