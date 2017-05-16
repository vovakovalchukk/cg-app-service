<?php

use CG\PurchaseOrder\PurchaseOrderItem\Repository;
use CG\PurchaseOrder\PurchaseOrderItem\StorageInterface;
use CG\PurchaseOrder\PurchaseOrderItem\Storage\Db;
use CG\PurchaseOrder\PurchaseOrderItem\Storage\Cache;
use CG\PurchaseOrder\PurchaseOrderItem\Mapper;

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
