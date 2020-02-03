<?php

use CG\Order\Shared\Item\Refund\Repository;
use CG\Order\Shared\Item\Refund\StorageInterface;
use CG\Order\Service\Item\Refund\Storage\Db;
use CG\Order\Service\Item\Refund\Storage\Cache;
use CG\Order\Shared\Item\Refund\Mapper;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class
            ],
            Repository::class => [
                'parameter' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ]
            ],
            Db::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => Mapper::class
                ]
            ]
        ]
    ]
];