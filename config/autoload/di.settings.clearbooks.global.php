<?php

use CG\Settings\Clearbooks\Customer\Service as ClearbooksCustomerService;
use CG\Settings\Clearbooks\Customer\Repository as ClearbooksCustomerRepository;
use CG\Settings\Clearbooks\Customer\Storage\Cache as ClearbooksCustomerCacheStorage;
use CG\Settings\Clearbooks\Customer\Storage\Db as ClearbooksCustomerDbStorage;


return [
    'di' => [
        'instance' => [
            ClearbooksCustomerService::class => array(
                'parameters' => array(
                    'repository' => ClearbooksCustomerRepository::class,
                )
            ),
            ClearbooksCustomerRepository::class => array(
                'parameter' => array(
                    'storage' => ClearbooksCustomerCacheStorage::class,
                    'repository' => ClearbooksCustomerDbStorage::class
                )
            ),
            ClearbooksCustomerDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
        ]
    ]
];