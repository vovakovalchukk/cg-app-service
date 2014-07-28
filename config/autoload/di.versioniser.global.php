<?php
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'Versioniser_OrderItemCollection_1' => OrderItemCollection\Versioniser1::class,
                'Versioniser_OrderItemEntity_1' => OrderItemEntity\Versioniser1::class,
                'Versioniser_TemplateCollection_1' => TemplateCollection\Versioniser1::class,
                'Versioniser_TemplateEntity_1' => TemplateEntity\Versioniser1::class,
            ],
            'Versioniser_OrderItemCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_1',
                ],
            ],
            'Versioniser_TemplateCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_TemplateEntity_1'
                ],
            ],
        ],
    ]
];