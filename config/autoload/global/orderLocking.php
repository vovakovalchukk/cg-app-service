<?php

use CG\Locking\Service as LockingService;
use CG\Order\Client\Gearman\Generator\LinkMatchingOrders as LinkMatchingOrdersGenerator;
use CG\Order\Locking\Service as OrderLockingService;
use CG\Order\Service\Callback\AfterOrderSave;
use CG\Order\Service\Filter\Storage\Cache as FilterCache;
use CG\Order\Service\Service as OrderService;
use CG\Order\Service\Storage\ElasticSearch as OrderElasticSearchStorage;
use CG\Order\Service\Storage\Persistent as OrderPersistentStorage;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'LockingServiceOrders' => LockingService::class,
            ],
            'LockingServiceOrders' => [
                'parameter' => [
                    'expireAfter' => 60,
                    'maxRetries' => 1,
                    'waitTime' => 5,
                ],
            ],
            OrderLockingService::class => [
                'parameters' => [
                    'repository' => OrderPersistentStorage::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'filterStorage' => FilterCache::class,
                    'lockingService' => 'LockingServiceOrders',
                    'afterOrderSave' => AfterOrderSave::class
                ],
            ],
            LinkMatchingOrdersGenerator::class => [
                'parameters' => [
                    'orderGearmanClient' => 'orderGearmanClient',
                ]
            ],
            'preferences' => [
                OrderService::class => OrderLockingService::class,
            ]
        ]
    ]
];