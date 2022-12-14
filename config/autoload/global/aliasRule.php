<?php

use CG\Settings\Shipping\Alias\Rule\Mapper;
use CG\Settings\Shipping\Alias\Rule\Storage\Cache;
use CG\Settings\Shipping\Alias\Rule\Storage\Db;
use CG\Settings\Shipping\Alias\Rule\StorageInterface;
use CG\Settings\Shipping\Alias\Rule\Repository;

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