<?php

use CG\Supplier\Mapper;
use CG\Supplier\Repository;
use CG\Supplier\Storage\Cache;
use CG\Supplier\Storage\Db;
use CG\Supplier\StorageInterface;

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