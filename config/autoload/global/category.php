<?php

use CG\Product\Category\Repository as CategoryRepository;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Storage\Cache as CategoryStorageCache;
use CG\Product\Category\Storage\Db as CategoryStorageDb;
use CG\Product\Category\StorageInterface as CategoryStorageInterface;
use CG\Product\Category\Mapper as CategoryMapper;

use CG\Product\Category\ExternalData\Repository as CategoryExternalRepository;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\ExternalData\Storage\Cache as CategoryExternalStorageCache;
use CG\Product\Category\ExternalData\Storage\Db as CategoryExternalStorageDb;
use CG\Product\Category\ExternalData\StorageInterface as CategoryExternalStorageInterface;
use CG\Product\Category\ExternalData\Mapper as CategoryExternalMapper;

use CG\Product\Category\Template\Repository as CategoryTemplateRepository;
use CG\Product\Category\Template\Service as CategoryTemplateService;
use CG\Product\Category\Template\Storage\Cache as CategoryTemplateStorageCache;
use CG\Product\Category\Template\Storage\Db as CategoryTemplateStorageDb;
use CG\Product\Category\Template\StorageInterface as CategoryTemplateStorageInterface;
use CG\Product\Category\Template\Mapper as CategoryTemplateMapper;

use CG\Amazon\Category\ExternalData\StorageInterface as AmazonChannelStorage;
use CG\Amazon\Category\ExternalData\Repository as AmazonChannelRepository;
use CG\Amazon\Category\ExternalData\Storage\Cache as AmazonChannelCacheStorage;
use CG\Amazon\Category\ExternalData\Storage\File as AmazonChannelFileStorage;
use CG\FileStorage\S3\Adapter as AmazonChannelFileAdapter;

use CG\Ebay\Category\ExternalData\ChannelService as EbayChannelService;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'AmazonChannelFileAdapter' => AmazonChannelFileAdapter::class,
            ],
            'preferences' => [
                CategoryStorageInterface::class => CategoryRepository::class,
                CategoryExternalStorageInterface::class => CategoryExternalRepository::class,
                CategoryTemplateStorageInterface::class => CategoryTemplateRepository::class,
                AmazonChannelStorage::class => AmazonChannelRepository::class,
            ],
            CategoryStorageDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => CategoryMapper::class
                ],
            ],
            CategoryRepository::class => [
                'parameters' => [
                    'storage' => CategoryStorageCache::class,
                    'repository' => CategoryStorageDb::class
                ]
            ],
            CategoryService::class => [
                'parameters' => [
                    'storage' => CategoryRepository::class
                ]
            ],
            CategoryExternalStorageDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => CategoryExternalMapper::class
                ],
            ],
            CategoryExternalRepository::class => [
                'parameters' => [
                    'storage' => CategoryExternalStorageCache::class,
                    'repository' => CategoryExternalStorageDb::class
                ]
            ],
            CategoryExternalService::class => [
                'parameters' => [
                    'storage' => CategoryExternalRepository::class
                ]
            ],
            CategoryTemplateStorageDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => CategoryTemplateMapper::class
                ],
            ],
            CategoryTemplateRepository::class => [
                'parameters' => [
                    'storage' => CategoryTemplateStorageCache::class,
                    'repository' => CategoryTemplateStorageDb::class
                ]
            ],
            CategoryTemplateService::class => [
                'parameters' => [
                    'storage' => CategoryTemplateRepository::class
                ]
            ],
            EbayChannelService::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            AmazonChannelRepository::class => [
                'parameters' => [
                    'storage' => AmazonChannelCacheStorage::class,
                    'repository' => AmazonChannelFileStorage::class,
                ],
            ],
            AmazonChannelFileStorage::class => [
                'parameters' => [
                    'fileStorage' => 'AmazonChannelFileAdapter',
                ],
            ],
            'AmazonChannelFileAdapter' => [
                'parameters' => [
                    'location' => 'orderhub-amazoncategoryexternaldata',
                ],
            ],
        ],
    ],
];
