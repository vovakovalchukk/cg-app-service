<?php
use CG\Stock\Location\Mapper;
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
                Mapper::class => TypedMapper::class,
            ],
            Service::class => [
                'parameters' => [
                    'repository' => LinkedReplacer::class,
                    'stockStorage' => StockRepository::class
                ]
            ],
            LinkedReplacer::class => [
                'parameters' => [
                    'locationStorage' => Repository::class,
                ],
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Cache::class,
                    'repository' => Db::class
                ]
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            TypedMapper::class => [
                'parameters' => [
                    'entityClass' => function() { return TypedEntity::class; }
                ],
            ],
        ],
    ],
];