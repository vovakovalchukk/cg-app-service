<?php
use CG\Product\Link\Repository;
use CG\Product\Link\Storage\Cache;
use CG\Product\Link\Storage\Db;
use CG\Product\Link\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class,
            ],
            Repository:: class => [
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