<?php
use CG\Product\Link\Repository;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\Service\Service;
use CG\Product\Link\Storage\Cache;
use CG\Product\Link\Storage\Db;
use CG\Product\Link\StorageInterface;
use CG\Product\LinkLeaf\Repository as LeafRepository;
use CG\Product\LinkLeaf\Storage\Cache as LeafCache;
use CG\Product\LinkLeaf\Storage\Db as LeafDb;
use CG\Product\LinkLeaf\StorageInterface as LeafStorageInterface;
use CG\Product\LinkNode\Repository as NodeRepository;
use CG\Product\LinkNode\Storage\Cache as NodeCache;
use CG\Product\LinkNode\Storage\Db as NodeDb;
use CG\Product\LinkNode\StorageInterface as NodeStorageInterface;
use CG\Stock\Location\Repository as StockLocationRepository;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Repository as StockRepository;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'InternalStockLocationService' => StockLocationService::class,
            ],
            'preferences' => [
                BaseService::class => Service::class,
                StorageInterface::class => Repository::class,
                LeafStorageInterface::class => LeafRepository::class,
                NodeStorageInterface::class => NodeRepository::class,
            ],
            Service::class => [
                'parameters' => [
                    'stockLocationStorage' => 'InternalStockLocationService',
                    'stockStorage' => StockRepository::class,
                ],
            ],
            'InternalStockLocationService' => [
                'parameters' => [
                    'repository' => StockLocationRepository::class,
                ],
            ],
            Repository:: class => [
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
            LeafRepository::class => [
                'parameters' => [
                    'storage' => LeafCache::class,
                    'repository' => LeafDb::class,
                ],
            ],
            LeafDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                ],
            ],
            NodeRepository::class => [
                'parameters' => [
                    'storage' => NodeCache::class,
                    'repository' => NodeDb::class,
                ],
            ],
            NodeDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                ],
            ],
        ],
    ],
];