<?php
use CG\Ebay\Product\CategoryDetail\External\Storage\Db as EbayDbStorage;
use CG\Ebay\Product\CategoryDetail\External\StorageInterface as EbayStorage;
use CG\Product\CategoryDetail\Repository;
use CG\Product\CategoryDetail\Storage\Cache;
use CG\Product\CategoryDetail\Storage\Db;
use CG\Product\CategoryDetail\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class,
                EbayStorage::class => EbayDbStorage::class,
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ],
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            EbayDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
        ],
    ],
];