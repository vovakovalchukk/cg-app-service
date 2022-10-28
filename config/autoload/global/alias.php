<?php

use CG\Order\Shared\Shipping\Method\Mapper as ShippingMethodMapper;
use CG\Settings\Shipping\Alias\Mapper;
use CG\Settings\Shipping\Alias\Storage\Cache;
use CG\Settings\Shipping\Alias\Storage\Db;
use CG\Settings\Shipping\Alias\Service;
use CG\Settings\Shipping\Alias\StorageInterface;
use CG\Settings\Shipping\Alias\Repository;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class
            ],
            Service::class => array(
                'parameters' => array(
                    'repository' => Repository::class,
                    'mapper' => Mapper::class
                )
            ),
            Mapper::class => array(
                'parameters' => array(
                    'shippingMethodMapper' => ShippingMethodMapper::class
                )
            ),
            Repository::class => array(
                'parameter' => array(
                    'storage' => Cache::class,
                    'repository' => Db::class
                )
            ),
            Db::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => Mapper::class
                )
            ),
        ],
    ],
];