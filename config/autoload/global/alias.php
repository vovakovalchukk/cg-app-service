<?php

use CG\Settings\Shipping\Alias\Mapper;
use CG\Settings\Shipping\Alias\Storage\Cache;
use CG\Settings\Shipping\Alias\Storage\Db;
use CG\Settings\Shipping\Alias\StorageInterface;
use CG\Settings\Shipping\Alias\Repository;

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
                ],
            ],
            Db::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => Mapper::class,
                ],
            ],
        ],
    ],
];