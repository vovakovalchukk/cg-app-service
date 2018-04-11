<?php
use CG\Product\AccountDetail\Repository;
use CG\Product\AccountDetail\Storage\Cache;
use CG\Product\AccountDetail\Storage\Db;
use CG\Product\AccountDetail\StorageInterface;

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