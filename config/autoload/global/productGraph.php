<?php
use CG\Product\Graph\Storage\Db;
use CG\Product\Graph\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Db::class,
            ],
            Db::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                ],
            ],
        ],
    ],
];