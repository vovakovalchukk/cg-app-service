<?php
use CG\Cache\Command\ValidateCollection as ValidateCollectionCommand;
use CG\Notification\Queue as NotificationQueue;
use CG\Redis\Queue as RedisQueue;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'NotificationQueue' => RedisQueue::class,
            ],
            NotificationQueue::class => [
                'parameters' => [
                    'queue' => 'NotificationQueue',
                ],
            ],
            'NotificationQueue' => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                    'keyPrefix' => function() { return 'NotificationQueue'; },
                ],
            ],
            ValidateCollectionCommand::class => [
                'parameters' => [
                    'validationQueue' => 'cache_validation_queue',
                ],
            ],
        ],
    ],
];
