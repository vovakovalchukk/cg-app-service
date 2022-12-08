<?php
use CG\Product\ProductSort\Storage\Db as DbStorage;
use CG\Product\ProductSort\StorageInterface;
use CG\Product\ProductSort\Mapper as ProductSortMapper;

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
                    'mapper' => ProductSortMapper::class
                ],
            ],
        ],
    ],
];