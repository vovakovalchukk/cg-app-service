<?php

use CG\Product\Category\Repository as CategoryRepository;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Storage\Cache as CategoryStorageCache;
use CG\Product\Category\Storage\Db as CategoryStorageDb;
use CG\Product\Category\StorageInterface as CategoryStorageInterface;
use CG\Product\Category\Mapper as CategoryMapper;

return [
    'di' => [
        'preferences' => [
            CategoryStorageInterface::class => CategoryRepository::class
        ],
        'instance' => [
            CategoryStorageDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => CategoryMapper::class
                ],
            ],
            CategoryRepository::class => [
                'parameters' => [
                    'storage' => CategoryStorageCache::class,
                    'repository' => CategoryStorageDb::class
                ]
            ],
            CategoryService::class => [
                'parameters' => [
                    'storage' => CategoryRepository::class
                ]
            ]
        ],
    ],
];
