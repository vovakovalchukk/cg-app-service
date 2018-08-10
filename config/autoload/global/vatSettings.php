<?php

use CG\Settings\Vat\Mapper as VatSettingsMapper;
use CG\Settings\Vat\StorageInterface as VatSettingsStorage;
use CG\Settings\Vat\Repository as VatSettingsRepository;
use CG\Settings\Vat\Storage\Db as VatSettingsDbStorage;
use CG\Settings\Vat\Storage\Cache as VatSettingsCacheStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                VatSettingsStorage::class => VatSettingsRepository::class,
            ],
            VatSettingsDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => VatSettingsMapper::class,
                ],
            ],
            VatSettingsRepository::class => [
                'parameters' => [
                    'storage' => VatSettingsCacheStorage::class,
                    'repository' => VatSettingsDbStorage::class,
                ],
            ]
        ]
    ]
];