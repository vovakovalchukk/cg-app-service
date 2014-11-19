<?php
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;
use CG\Slim\Versioning\ProductCollection;
use CG\Slim\Versioning\ProductEntity;
use CG\Slim\Versioning\AliasSettingsEntity;
use CG\Slim\Versioning\AliasSettingsCollection;
use CG\Slim\Versioning\ListingEntity;
use CG\Slim\Versioning\UnimportedListingEntity;
use CG\Slim\Versioning\UnimportedListingCollection;

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
                'Versioniser_ProductCollection_3' => ProductCollection\Versioniser3::class,
                'Versioniser_ProductEntity_3' => ProductEntity\Versioniser3::class,
                'Versioniser_AliasSettingsEntity_1' => AliasSettingsEntity\Versioniser1::class,
                'Versioniser_AliasSettingsCollection_1' => AliasSettingsCollection\Versioniser1::class,
                'Versioniser_UnimportedListingEntity_1' => UnimportedListingEntity\Versioniser1::class,
                'Versioniser_UnimportedListingCollection_1' => UnimportedListingCollection\Versioniser1::class,
                'Versioniser_UnimportedListingEntity_2' => UnimportedListingEntity\Versioniser2::class,
                'Versioniser_UnimportedListingCollection_2' => UnimportedListingCollection\Versioniser2::class,
                'Versioniser_ListingEntity_1' => ListingEntity\Versioniser1::class,
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
            'Versioniser_ProductCollection_3' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_3'
                ],
            ],
            'Versioniser_AliasSettingsCollection_1' => [
                'parameter' => [
                    'aliasVersioniser1' => 'Versioniser_AliasSettingsEntity_1'
                ],
            ],
            'Versioniser_UnimportedListingCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_UnimportedListingEntity_1'
                ],
            ],
            'Versioniser_UnimportedListingEntity_2' => [
                'parameter' => [
                    'accountClient' => AccountApiStorage::class
                ]
            ],
            'Versioniser_UnimportedListingCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_UnimportedListingEntity_2'
                ],
            ],
        ],
    ]
];
