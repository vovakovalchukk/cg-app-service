<?php

use CG\Order\Service\OrderLink\Storage\Db as OrderLinkDbStorage;
use CG\Order\Shared\OrderLink\Mapper as OrderLinkMapper;
use CG\Order\Shared\OrderLink\StorageInterface as OrderLinkStorage;
use CG\Order\Client\Gearman\Generator\LinkMatchingOrders as OrderLinkGenerator;

return [
    'di' => [
        'instance' => [
            OrderLinkDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => OrderLinkMapper::class
                ]
            ],
            'preferences' => [
                OrderLinkStorage::class => OrderLinkDbStorage::class,
            ],
            OrderLinkGenerator::class => [
                'parameter' => [
                    'orderGearmanClient' => 'orderGearmanClient'
                ]
            ]
        ]
    ]
];