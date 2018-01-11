<?php

use CG\Product\Category\Repository as CategoryRepository;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Storage\Cache as CategoryStorageCache;
use CG\Product\Category\Storage\Db as CategoryStorageDb;
use CG\Product\Category\StorageInterface as CategoryStorageInterface;
use CG\Product\Category\Mapper as CategoryMapper;

use CG\Product\Category\ExternalData\Repository as CategoryExternalRepository;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\ExternalData\Storage\Cache as CategoryExternalStorageCache;
use CG\Product\Category\ExternalData\Storage\Db as CategoryExternalStorageDb;
use CG\Product\Category\ExternalData\StorageInterface as CategoryExternalStorageInterface;
use CG\Product\Category\ExternalData\Mapper as CategoryExternalMapper;

return [
    'di' => [
        'preferences' => [
            CategoryStorageInterface::class => CategoryRepository::class,
            CategoryExternalStorageInterface::class => CategoryExternalRepository::class,
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
            ],
            CategoryExternalStorageDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => CategoryExternalMapper::class
                ],
            ],
            CategoryExternalRepository::class => [
                'parameters' => [
                    'storage' => CategoryExternalStorageCache::class,
                    'repository' => CategoryExternalStorageDb::class
                ]
            ],
            CategoryExternalService::class => [
                'parameters' => [
                    'storage' => CategoryExternalRepository::class
                ]
            ]
        ],
    ],
];
