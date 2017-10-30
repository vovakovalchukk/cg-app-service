<?php
use CG\Product\Graph\Repository;
use CG\Product\Graph\Storage\Cache;
use CG\Product\Graph\Storage\Db;
use CG\Product\Graph\StorageInterface;

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
                    'readSql' => 'ReadSql',
                ],
            ],
        ],
    ],
];