<?php
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Slim\Versioning\ListingCollection;
use CG\Slim\Versioning\ListingEntity;
use CG\Slim\Versioning\OrderCollection;
use CG\Slim\Versioning\OrderEntity;
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;
use CG\Slim\Versioning\ProductCollection;
use CG\Slim\Versioning\ProductEntity;
use CG\Slim\Versioning\AliasSettingsCollection;
use CG\Slim\Versioning\AliasSettingsEntity;
use CG\Slim\Versioning\UnimportedListingCollection;
use CG\Slim\Versioning\UnimportedListingEntity;
use CG\Slim\Versioning\InvoiceSettingsCollection;
use CG\Slim\Versioning\InvoiceSettingsEntity;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'Versioniser_ListingCollection_1' => ListingCollection\Versioniser1::class,
                'Versioniser_ListingEntity_1' => ListingEntity\Versioniser1::class,
                'Versioniser_ListingCollection_2' => ListingCollection\Versioniser2::class,
                'Versioniser_ListingEntity_2' => ListingEntity\Versioniser2::class,
                'Versioniser_ListingCollection_3' => ListingCollection\Versioniser3::class,
                'Versioniser_ListingEntity_3' => ListingEntity\Versioniser3::class,
                'Versioniser_OrderCollection_1' => OrderCollection\Versioniser1::class,
                'Versioniser_OrderEntity_1' => OrderEntity\Versioniser1::class,
                'Versioniser_OrderCollection_2' => OrderCollection\Versioniser2::class,
                'Versioniser_OrderEntity_2' => OrderEntity\Versioniser2::class,
                'Versioniser_OrderCollection_3' => OrderCollection\Versioniser3::class,
                'Versioniser_OrderEntity_3' => OrderEntity\Versioniser3::class,
                'Versioniser_OrderCollection_4' => OrderCollection\Versioniser4::class,
                'Versioniser_OrderEntity_4' => OrderEntity\Versioniser4::class,
                'Versioniser_OrderCollection_5' => OrderCollection\Versioniser5::class,
                'Versioniser_OrderEntity_5' => OrderEntity\Versioniser5::class,
                'Versioniser_OrderCollection_6' => OrderCollection\Versioniser6::class,
                'Versioniser_OrderEntity_6' => OrderEntity\Versioniser6::class,
                'Versioniser_OrderItemCollection_1' => OrderItemCollection\Versioniser1::class,
                'Versioniser_OrderItemEntity_1' => OrderItemEntity\Versioniser1::class,
                'Versioniser_OrderItemCollection_2' => OrderItemCollection\Versioniser2::class,
                'Versioniser_OrderItemEntity_2' => OrderItemEntity\Versioniser2::class,
                'Versioniser_OrderItemCollection_3' => OrderItemCollection\Versioniser3::class,
                'Versioniser_OrderItemEntity_3' => OrderItemEntity\Versioniser3::class,
                'Versioniser_OrderItemCollection_4' => OrderItemCollection\Versioniser3::class,
                'Versioniser_OrderItemEntity_4' => OrderItemEntity\Versioniser3::class,
                'Versioniser_TemplateCollection_1' => TemplateCollection\Versioniser1::class,
                'Versioniser_TemplateEntity_1' => TemplateEntity\Versioniser1::class,
                'Versioniser_ProductCollection_1' => ProductCollection\Versioniser1::class,
                'Versioniser_ProductEntity_1' => ProductEntity\Versioniser1::class,
                'Versioniser_ProductCollection_2' => ProductCollection\Versioniser2::class,
                'Versioniser_ProductEntity_2' => ProductEntity\Versioniser2::class,
                'Versioniser_ProductCollection_3' => ProductCollection\Versioniser3::class,
                'Versioniser_ProductEntity_3' => ProductEntity\Versioniser3::class,
                'Versioniser_ProductCollection_4' => ProductCollection\Versioniser4::class,
                'Versioniser_ProductEntity_4' => ProductEntity\Versioniser4::class,
                'Versioniser_AliasSettingsCollection_1' => AliasSettingsCollection\Versioniser1::class,
                'Versioniser_AliasSettingsEntity_1' => AliasSettingsEntity\Versioniser1::class,
                'Versioniser_UnimportedListingCollection_1' => UnimportedListingCollection\Versioniser1::class,
                'Versioniser_UnimportedListingEntity_1' => UnimportedListingEntity\Versioniser1::class,
                'Versioniser_UnimportedListingCollection_2' => UnimportedListingCollection\Versioniser2::class,
                'Versioniser_UnimportedListingEntity_2' => UnimportedListingEntity\Versioniser2::class,
                'Versioniser_UnimportedListingCollection_3' => UnimportedListingCollection\Versioniser3::class,
                'Versioniser_UnimportedListingEntity_3' => UnimportedListingEntity\Versioniser3::class,
                'Versioniser_UnimportedListingCollection_4' => UnimportedListingCollection\Versioniser4::class,
                'Versioniser_UnimportedListingEntity_4' => UnimportedListingEntity\Versioniser4::class,
                'Versioniser_InvoiceSettingsCollection_1' => InvoiceSettingsCollection\Versioniser1::class,
                'Versioniser_InvoiceSettings_1' => InvoiceSettingsEntity\Versioniser1::class,
            ],
            'Versioniser_ListingCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ListingEntity_1',
                ],
            ],
            'Versioniser_ListingCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ListingEntity_2',
                ],
            ],
            'Versioniser_OrderCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_1',
                ],
            ],
            'Versioniser_OrderCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_2',
                ],
            ],
            'Versioniser_OrderCollection_3' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_3',
                ],
            ],
            'Versioniser_OrderCollection_4' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_4',
                ],
            ],
            'Versioniser_OrderCollection_5' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_5',
                ],
            ],
            'Versioniser_OrderCollection_6' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_6',
                ],
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
            'Versioniser_OrderItemCollection_3' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_3',
                ],
            ],
            'Versioniser_OrderItemCollection_4' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderItemEntity_4',
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
            'Versioniser_AliasSettingsCollection_3' => [
                'parameter' => [
                    'aliasVersioniser3' => 'Versioniser_AliasSettingsEntity_3'
                ],
            ],
            'Versioniser_UnimportedListingCollection_3' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_UnimportedListingEntity_3'
                ],
            ],
            'Versioniser_InvoiceSettingsCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_InvoiceSettings_1'
                ],
            ],
        ],
    ]
];
