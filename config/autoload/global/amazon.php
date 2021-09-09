<?php
use CG\Amazon\ListingImport as AmazonListingImport;

use CG\Amazon\Feed\Mapper as FeedMapper;
use CG\Amazon\Feed\Message\Mapper as FeedMessageMapper;
use CG\Amazon\Feed\Message\Repository as FeedMessageRepository;
use CG\Amazon\Feed\Message\Service as FeedMessageService;
use CG\Amazon\Feed\Message\Storage\Db as FeedMessageDb;
use CG\Amazon\Feed\Message\Storage\Cache as FeedMessageCache;
use CG\Amazon\Feed\Message\StorageInterface as FeedMessageStorage;
use CG\Amazon\Feed\Service as FeedService;
use CG\Amazon\Feed\Storage\Db as FeedDb;
use CG\Amazon\Feed\StorageInterface as FeedStorage;

return [
    'di' => [
        'preferences' => [
            FeedMessageStorage::class => FeedMessageRepository::class,
            FeedStorage::class => FeedDb::class,
        ],
        'instance' => [
            AmazonListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'amazonGearmanClient'
                ]
            ],
            FeedMessageDb::class => [
                'parameters' => [
                    'readSql' => 'amazonReadCGSql',
                    'fastReadSql' => 'amazonFastReadCGSql',
                    'writeSql' => 'amazonWriteCGSql',
                    'mapper' => FeedMessageMapper::class,
                ]
            ],
            FeedMessageRepository::class => [
                'parameters' => [
                    'storage' => FeedMessageCache::class,
                    'repository' => FeedMessageDb::class
                ]
            ],
            FeedMessageService::class => [
                'parameters' => [
                    'storage' => FeedMessageRepository::class,
                ]
            ],
            FeedDb::class => [
                'parameter' => [
                    'readSql' => 'amazonReadCGSql',
                    'fastReadSql' => 'amazonFastReadCGSql',
                    'writeSql' => 'amazonWriteCGSql',
                    'mapper' => FeedMapper::class,
                ]
            ],
            FeedService::class => [
                'parameters' => [
                    'storage' => FeedDb::class,
                ]
            ]
        ],
    ],
];