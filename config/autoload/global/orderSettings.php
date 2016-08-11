<?php

use CG\Settings\Order\Mapper as OrderSettingsMapper;
use CG\Settings\Order\StorageInterface as OrderSettingsStorage;
use CG\Settings\Order\Repository as OrderSettingsRepository;
use CG\Settings\Order\Storage\Db as OrderSettingsDbStorage;
use CG\Settings\Order\Storage\Cache as OrderSettingsCacheStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                OrderSettingsStorage::class => OrderSettingsRepository::class,
            ],
            OrderSettingsDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => OrderSettingsMapper::class,
                ],
            ],
            OrderSettingsRepository::class => [
                'parameters' => [
                    'storage' => OrderSettingsCacheStorage::class,
                    'repository' => OrderSettingsDbStorage::class,
                ],
            ]
        ]
    ]
];