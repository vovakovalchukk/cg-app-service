<?php

use CG\Usage\Storage\Db as UsageDb;
use CG\Usage\Aggregate\Storage\Db as UsageAggregateDb;
use CG\Usage\Storage\Redis as UsageRedis;
use CG\Usage\Repository as UsageRepository;
use CG\Usage\StorageInterface as UsageStorageInterface;
use CG\OrganisationUnit\Service;
use CG\OrganisationUnit\Storage\Api;
use CG\Slim\Usage\Count;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'UsageOrganisationUnitService' => Service::class
            ],
            UsageDb::class=> [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageAggregateDb::class=> [
                'parameter'=> [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageRepository::class => [
                'parameter' => [
                    'storage' => UsageRedis::class,
                    'repository' => UsageDb::class
                ]
            ],
            UsageRedis::class => [
                'parameter' => [
                    'client' => 'unreliable_redis',
                    'aggregateStorage' => UsageAggregateDb::class
                ]
            ],
            Count::class => [
                'parameter' => [
                    'organisationUnitService' => 'UsageOrganisationUnitService'
                ]
            ],
            Api::class => [
                'parameter' => [
                    'client' => 'directory_guzzle'
                ]
            ],
            'UsageOrganisationUnitService' => [
                'parameter' => [
                    'repository' => Api::class
                ]
            ],
            'preferences' => [
                UsageStorageInterface::class => UsageRepository::class
            ]
        ]
    ]
];