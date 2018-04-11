<?php
use CG\Product\ChannelDetail\StorageInterface;
use CG\Product\ChannelDetail\Storage\Cache;
use CG\Product\ChannelDetail\Storage\Db;
use CG\Product\ChannelDetail\Repository;

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