<?php
use CG\Product\PickingLocation\Storage\Db as DbStorage;
use CG\Product\PickingLocation\StorageInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => DbStorage::class,
            ],
            DbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                ],
            ],
        ],
    ],
];