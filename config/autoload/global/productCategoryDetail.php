<?php
use CG\Product\CategoryDetail\Repository;
use CG\Product\CategoryDetail\Storage\Cache;
use CG\Product\CategoryDetail\Storage\Db;
use CG\Product\CategoryDetail\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class,
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ],
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
        ],
    ],
];