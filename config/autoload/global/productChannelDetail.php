<?php
use CG\Ebay\Product\ChannelDetail\External\Storage\Db as EbayDbStorage;
use CG\Ebay\Product\ChannelDetail\External\StorageInterface as EbayStorage;
use CG\Product\ChannelDetail\Repository;
use CG\Product\ChannelDetail\Storage\Cache;
use CG\Product\ChannelDetail\Storage\Db;
use CG\Product\ChannelDetail\StorageInterface;

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