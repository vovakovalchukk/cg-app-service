<?php
use CG\FileStorage\S3\Adapter;
use CG\Locking\Service as LockingService;
use CG\Stock\Audit\Adjustment\Mapper as AdjustmentMapper;
use CG\Stock\Audit\Adjustment\Storage\Db as AdjustmentDbStorage;
use CG\Stock\Audit\Adjustment\Storage\FileStorage as AdjustmentFileStorage;
use CG\Stock\Audit\Adjustment\Storage\FileStorage\Cache as AdjustmentFileCache;
use CG\Stock\Audit\Combined\Storage\FileStorage as CombinedFileStorage;
use CG\Stock\Command\MigrateStockAuditAdjustments;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'AdjustmentAWSS3Storage' => Adapter::class,
                'LockingServiceStockAdjustmentMigration' => LockingService::class,
            ],
            MigrateStockAuditAdjustments::class => [
                'parameters' => [
                    'storage' => AdjustmentDbStorage::class,
                    'archive' => AdjustmentFileStorage::class,
                    'lockingService' => 'LockingServiceStockAdjustmentMigration',
                ],
            ],
            AdjustmentDbStorage::class => [
                'parameters' => [
                    'readSql' => 'cg_appReadSql',
                    'fastReadSql' => 'cg_appFastReadSql',
                    'writeSql' => 'cg_appWriteSql',
                    'mapper' => AdjustmentMapper::class,
                ],
            ],
            AdjustmentFileStorage::class => [
                'parameters' => [
                    'storageAdapter' => 'AdjustmentAWSS3Storage',
                ],
            ],
            'AdjustmentAWSS3Storage' => [
                'parameters' => [
                    'location' => 'channelgrabber-stockaudit',
                ],
            ],
            AdjustmentFileCache::class => [
                'parameters' => [
                    'predis' => 'stock-audit-file-cache_redis',
                ],
            ],
            CombinedFileStorage::class => [
                'parameters' => [
                    'auditAdjustmentStorage' => AdjustmentDbStorage::class,
                    'auditAdjustmentFileStorage' => AdjustmentFileStorage::class,
                ],
            ],
            'LockingServiceStockAdjustmentMigration' => [
                'parameters' => [
                    'expireAfter' => 21600, // 6 hours
                    'maxRetries' => 0,
                ],
            ],
        ],
    ],
];