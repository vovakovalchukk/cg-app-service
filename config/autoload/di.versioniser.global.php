<?php
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;
use CG\Slim\Versioning\ProductCollection;
use CG\Slim\Versioning\ProductEntity;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'Versioniser_OrderItemCollection_1' => OrderItemCollection\Versioniser1::class,
                'Versioniser_OrderItemEntity_1' => OrderItemEntity\Versioniser1::class,
                'Versioniser_OrderItemCollection_2' => OrderItemCollection\Versioniser2::class,
                'Versioniser_OrderItemEntity_2' => OrderItemEntity\Versioniser2::class,
                'Versioniser_TemplateCollection_1' => TemplateCollection\Versioniser1::class,
                'Versioniser_TemplateEntity_1' => TemplateEntity\Versioniser1::class,
                'Versioniser_ProductCollection_1' => ProductCollection\Versioniser1::class,
                'Versioniser_ProductEntity_1' => ProductEntity\Versioniser1::class,
                'Versioniser_ProductCollection_2' => ProductCollection\Versioniser2::class,
                'Versioniser_ProductEntity_2' => ProductEntity\Versioniser2::class,
            ],
            'Versioniser_OrderItemCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_1',
                ],
            ],
            'Versioniser_OrderItemCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_2',
                ],
            ],
            'Versioniser_TemplateCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_TemplateEntity_1'
                ],
            ],
            'Versioniser_ProductCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_1'
                ],
            ],
            'Versioniser_ProductCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_2'
                ],
            ],
        ],
    ]
];