<?php
use CG\Listing\Template\Mapper;
use CG\Listing\Template\Repository;
use CG\Listing\Template\Storage\Cache;
use CG\Listing\Template\Storage\Db;
use CG\Listing\Template\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class,
            ],
            Repository::class => [
                'parameters' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ],
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'listingsReadSql',
                    'fastReadSql' => 'listingsFastReadSql',
                    'writeSql' => 'listingsWriteSql',
                    'mapper' => Mapper::class,
                ],
            ],
        ],
    ],
];