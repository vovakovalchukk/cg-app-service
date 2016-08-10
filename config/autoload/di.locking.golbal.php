<?php
use CG\Locking\StorageInterface as LockingStorage;
use CG\Order\Shared\Mapper as OrderMapper;
use CG\Redis\Locking\Storage as LockingRedisStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                LockingStorage::class => LockingRedisStorage::class,
            ],
        ],
    ],
];
