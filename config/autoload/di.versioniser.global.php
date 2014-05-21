<?php
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'Versioniser_OrderItemCollection_1' => OrderItemCollection\Versioniser1::class,
                'Versioniser_OrderItemEntity_1' => OrderItemEntity\Versioniser1::class,
            ],
            'Versioniser_OrderItemCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_1',
                ],
            ],
            'Versioniser_OrderItemEntity_1' => [
                'parameter' => [
                    'service' => 'ItemCollectionService'
                ],
            ],
        ],
    ]
];