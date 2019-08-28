<?php
use CG\Locking\Service as LockingService;
use CG\Stock\Audit\Adjustment\Mapper as AdjustmentMapper;
use CG\Stock\Audit\Adjustment\Storage\Db as AdjustmentDbStorage;
use CG\Stock\Audit\Adjustment\Storage\ArchiveDb as AdjustmentArchiveDbStorage;
use CG\Stock\Command\MigrateStockAuditAdjustments;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'LockingServiceStockAdjustmentMigration' => LockingService::class,
            ],
            MigrateStockAuditAdjustments::class => [
                'parameters' => [
                    'storage' => AdjustmentDbStorage::class,
                    'archive' => AdjustmentArchiveDbStorage::class,
                    'predis' => 'reliable_redis',
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
            AdjustmentArchiveDbStorage::class => [
                'parameters' => [
                    'readSql' => 'channelgrabber-stockauditSql',
                    'fastReadSql' => 'channelgrabber-stockauditSql',
                    'writeSql' => 'channelgrabber-stockauditSql',
                    'mapper' => AdjustmentMapper::class,
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