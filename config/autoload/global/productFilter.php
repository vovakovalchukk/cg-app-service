<?php
use CG\Product\ProductFilter\Storage\Db as DbStorage;
use CG\Product\ProductFilter\StorageInterface;
use CG\Product\ProductFilter\Mapper as ProductFilterMapper;

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
                    'mapper' => ProductFilterMapper::class
                ],
            ],
        ],
    ],
];