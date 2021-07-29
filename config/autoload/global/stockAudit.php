<?php

use CG\Locking\Service as LockingService;
use CG\Stock\Audit\Adjustment\Mapper as AdjustmentMapper;
use CG\Stock\Audit\Adjustment\Related\Mapper as AdjustmentRelatedMapper;
use CG\Stock\Audit\Adjustment\Related\Storage\ArchiveDb as AdjustmentRelatedArchiveDbStorage;
use CG\Stock\Audit\Adjustment\Related\Storage\Db as AdjustmentRelatedDbStorage;
use CG\Stock\Audit\Adjustment\Storage\ArchiveDb as AdjustmentArchiveDbStorage;
use CG\Stock\Audit\Adjustment\Storage\Db as AdjustmentDbStorage;
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
                    'relatedStorage' => AdjustmentRelatedDbStorage::class,
                    'relatedArchive' => AdjustmentRelatedArchiveDbStorage::class,
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
            AdjustmentRelatedDbStorage::class => [
                'parameters' => [
                    'readSql' => 'cg_appReadSql',
                    'fastReadSql' => 'cg_appFastReadSql',
                    'writeSql' => 'cg_appWriteSql',
                    'mapper' => AdjustmentRelatedMapper::class,
                ],
            ],
            AdjustmentRelatedArchiveDbStorage::class => [
                'parameters' => [
                    'readSql' => 'channelgrabber-stockauditSql',
                    'fastReadSql' => 'channelgrabber-stockauditSql',
                    'writeSql' => 'channelgrabber-stockauditSql',
                    'mapper' => AdjustmentRelatedMapper::class,
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