<?php

use CG\Reporting\Order\StorageInterface;
use CG\Reporting\Order\Storage\Db as DbStorage;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => DbStorage::class
            ]
        ]
    ]
];
