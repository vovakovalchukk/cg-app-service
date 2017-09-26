<?php

use CG\PurchaseOrder\Repository;
use CG\PurchaseOrder\StorageInterface;
use CG\PurchaseOrder\Storage\Db;
use CG\PurchaseOrder\Storage\Cache;
use CG\PurchaseOrder\Mapper;

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
