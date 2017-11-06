<?php
use CG\Product\Link\Repository;
use CG\Product\Link\Service as BaseService;
use CG\Product\Link\Service\Service;
use CG\Product\Link\Storage\Cache;
use CG\Product\Link\Storage\Db;
use CG\Product\Link\StorageInterface;
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
        ],
    ],
];