<?php
use CG\Product\Link\Service as ProductLinkService;
use CG\Stock\Location\Repository;
use CG\Stock\Location\Service;
use CG\Stock\Location\Storage\Cache;
use CG\Stock\Location\Storage\Db;
use CG\Stock\Location\Storage\LinkedReplacer;
use CG\Stock\Location\StorageInterface;
use CG\Stock\Location\TypedEntity;
use CG\Stock\Location\TypedMapper;
use CG\Stock\Repository as StockRepository;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => LinkedReplacer::class,
            ],
            Service::class => [
                'parameters' => [
                    'repository' => LinkedReplacer::class,
                    'stockStorage' => StockRepository::class,
                ],
            ],
            LinkedReplacer::class => [
                'parameters' => [
                    // Note: using Db storage directly as Cache has proven unreliable
                    'locationStorage' => Db::class,
                    'stockStorage' => StockRepository::class,
                ],
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ],
            ],
            Cache::class => [
                'parameters' => [
                    'mapper' => TypedMapper::class,
                ],
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => TypedMapper::class,
                ],
            ],
            TypedMapper::class => [
                'parameters' => [
                    'stockStorage' => StockRepository::class,
                    'productLinkService' => ProductLinkService::class,
                    'entityClass' => function() { return TypedEntity::class; },
                ],
            ],
        ],
    ],
];