<?php

use CG\Product\ProductSort\Mapper;
use CG\Product\ProductSort\Repository;
use CG\Product\ProductSort\StorageInterface;
use CG\Product\ProductSort\Storage\Db as DbStorage;
use CG\Product\ProductSort\Storage\Cache as CacheStorage;


return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => DbStorage::class,
            ],
            DbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => Mapper::class
                ],
            ],
            Repository::class => [
                'parameter' => [
                    'storage' => CacheStorage::class,
                    'repository' => DbStorage::class
                ]
            ],
        ],
    ],
];