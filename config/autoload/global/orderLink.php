<?php

use CG\Order\Service\OrderLink\Storage\Cache as OrderLinkCacheStorage;
use CG\Order\Service\OrderLink\Storage\Db as OrderLinkDbStorage;
use CG\Order\Shared\OrderLink\Mapper as OrderLinkMapper;
use CG\Order\Shared\OrderLink\Repository as OrderLinkRepository;
use CG\Order\Shared\OrderLink\StorageInterface as OrderLinkStorage;

return [
    'di' => [
        'instance' => [
            OrderLinkRepository::class => [
                'parameter' => [
                    'storage' => OrderLinkCacheStorage::class,
                    'repository' => OrderLinkDbStorage::class
                ]
            ],
            OrderLinkDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => OrderLinkMapper::class
                ]
            ],
            'preferences' => [
                OrderLinkStorage::class => OrderLinkRepository::class,
            ]
        ]
    ]
];