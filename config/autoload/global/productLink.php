<?php
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\Service\Service;
use CG\Product\Link\Storage\Db;
use CG\Product\Link\StorageInterface;
use CG\Product\LinkLeaf\Storage\Db as LeafDb;
use CG\Product\LinkLeaf\StorageInterface as LeafStorageInterface;
use CG\Product\LinkNode\Storage\Db as NodeDb;
use CG\Product\LinkNode\StorageInterface as NodeStorageInterface;
use CG\Stock\Location\Repository as StockLocationRepository;
use CG\Stock\Location\Service\Service as StockLocationService;
use CG\Stock\Repository as StockRepository;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'InternalStockLocationService' => StockLocationService::class,
            ],
            'preferences' => [
                BaseService::class => Service::class,
                StorageInterface::class => Db::class,
                LeafStorageInterface::class => LeafDb::class,
                NodeStorageInterface::class => NodeDb::class,
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
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            LeafDb::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
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