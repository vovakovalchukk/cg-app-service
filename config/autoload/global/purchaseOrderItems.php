<?php

use CG\PurchaseOrder\Item\Repository;
use CG\PurchaseOrder\Item\StorageInterface;
use CG\PurchaseOrder\Item\Storage\Db;
use CG\PurchaseOrder\Item\Storage\Cache;
use CG\PurchaseOrder\Item\Mapper;

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
