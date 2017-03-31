<?php

use CG\Order\Service\ShipmentMetadata\Storage\Cache as ShipmentMetadataCacheStorage;
use CG\Order\Service\ShipmentMetadata\Storage\Db as ShipmentMetadataDbStorage;
use CG\Order\Shared\ShipmentMetadata\Mapper as ShipmentMetadataMapper;
use CG\Order\Shared\ShipmentMetadata\Repository as ShipmentMetadataRepository;
use CG\Order\Shared\ShipmentMetadata\StorageInterface as ShipmentMetadataStorage;

return [
    'di' => [
        'instance' => [
            ShipmentMetadataRepository::class => [
                'parameter' => [
                    'storage' => ShipmentMetadataCacheStorage::class,
                    'repository' => ShipmentMetadataDbStorage::class
                ]
            ],
            ShipmentMetadataDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => ShipmentMetadataMapper::class
                ]
            ],
            'preferences' => [
                ShipmentMetadataStorage::class => ShipmentMetadataRepository::class,
            ]
        ]
    ]
];