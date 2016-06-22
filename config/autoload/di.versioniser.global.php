<?php
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Slim\Versioning\AliasSettingsCollection;
use CG\Slim\Versioning\AliasSettingsEntity;
use CG\Slim\Versioning\InvoiceSettingsCollection;
use CG\Slim\Versioning\InvoiceSettingsEntity;
use CG\Slim\Versioning\ListingCollection;
use CG\Slim\Versioning\ListingEntity;
use CG\Slim\Versioning\ListingStatusHistoryCollection;
use CG\Slim\Versioning\ListingStatusHistoryEntity;
use CG\Slim\Versioning\OrderCollection;
use CG\Slim\Versioning\OrderEntity;
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\OrderLabelCollection;
use CG\Slim\Versioning\OrderLabelEntity;
use CG\Slim\Versioning\ProductCollection;
use CG\Slim\Versioning\ProductEntity;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;
use CG\Slim\Versioning\TrackingCollection;
use CG\Slim\Versioning\TrackingEntity;
use CG\Slim\Versioning\UnimportedListingCollection;
use CG\Slim\Versioning\UnimportedListingEntity;

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
                'Versioniser_ListingCollection_4' => ListingCollection\Versioniser4::class,
                'Versioniser_ListingEntity_4' => ListingEntity\Versioniser4::class,
                'Versioniser_ListingCollection_5' => ListingCollection\Versioniser5::class,
                'Versioniser_ListingEntity_5' => ListingEntity\Versioniser5::class,
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
                'Versioniser_OrderCollection_7' => OrderCollection\Versioniser7::class,
                'Versioniser_OrderEntity_7' => OrderEntity\Versioniser7::class,
                'Versioniser_OrderCollection_8' => OrderCollection\Versioniser8::class,
                'Versioniser_OrderEntity_8' => OrderEntity\Versioniser8::class,
                'Versioniser_OrderItemCollection_1' => OrderItemCollection\Versioniser1::class,
                'Versioniser_OrderItemEntity_1' => OrderItemEntity\Versioniser1::class,
                'Versioniser_OrderItemCollection_2' => OrderItemCollection\Versioniser2::class,
                'Versioniser_OrderItemEntity_2' => OrderItemEntity\Versioniser2::class,
                'Versioniser_OrderItemCollection_3' => OrderItemCollection\Versioniser3::class,
                'Versioniser_OrderItemEntity_3' => OrderItemEntity\Versioniser3::class,
                'Versioniser_OrderItemCollection_4' => OrderItemCollection\Versioniser4::class,
                'Versioniser_OrderItemEntity_4' => OrderItemEntity\Versioniser4::class,
                'Versioniser_OrderItemCollection_5' => OrderItemCollection\Versioniser5::class,
                'Versioniser_OrderItemEntity_5' => OrderItemEntity\Versioniser5::class,
                'Versioniser_OrderItemCollection_6' => OrderItemCollection\Versioniser6::class,
                'Versioniser_OrderItemEntity_6' => OrderItemEntity\Versioniser6::class,
                'Versioniser_OrderItemCollection_7' => OrderItemCollection\Versioniser7::class,
                'Versioniser_OrderItemEntity_7' => OrderItemEntity\Versioniser7::class,
                'Versioniser_OrderItemCollection_8' => OrderItemCollection\Versioniser8::class,
                'Versioniser_OrderItemEntity_8' => OrderItemEntity\Versioniser8::class,
                'Versioniser_OrderLabelCollection_1' => OrderLabelCollection\Versioniser1::class,
                'Versioniser_OrderLabelEntity_1' => OrderLabelEntity\Versioniser1::class,
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
                'Versioniser_ProductCollection_5' => ProductCollection\Versioniser5::class,
                'Versioniser_ProductEntity_5' => ProductEntity\Versioniser5::class,
                'Versioniser_ProductCollection_6' => ProductCollection\Versioniser6::class,
                'Versioniser_ProductEntity_6' => ProductEntity\Versioniser6::class,
                'Versioniser_ProductCollection_7' => ProductCollection\Versioniser7::class,
                'Versioniser_ProductEntity_7' => ProductEntity\Versioniser7::class,
                'Versioniser_AliasSettingsCollection_1' => AliasSettingsCollection\Versioniser1::class,
                'Versioniser_AliasSettingsEntity_1' => AliasSettingsEntity\Versioniser1::class,
                'Versioniser_AliasSettingsCollection_2' => AliasSettingsCollection\Versioniser2::class,
                'Versioniser_AliasSettingsEntity_2' => AliasSettingsEntity\Versioniser2::class,
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
                'Versioniser_InvoiceSettingsCollection_2' => InvoiceSettingsCollection\Versioniser2::class,
                'Versioniser_InvoiceSettings_2' => InvoiceSettingsEntity\Versioniser2::class,
                'Versioniser_ListingStatusHistoryCollection_1' => ListingStatusHistoryCollection\Versioniser1::class,
                'Versioniser_ListingStatusHistoryEntity_1' => ListingStatusHistoryEntity\Versioniser1::class,
                'Versioniser_TrackingCollection_1' => TrackingCollection\Versioniser1::class,
                'Versioniser_OrderTrackingCollection_1' => TrackingCollection\Versioniser1::class,
                'Versioniser_OrderTrackingEntity_1' => TrackingEntity\Versioniser1::class,
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
            'Versioniser_OrderCollection_7' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_7',
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
            'Versioniser_OrderLabelCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderLabelEntity_1',
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
            'Versioniser_ProductCollection_4' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_4'
                ],
            ],
            'Versioniser_ProductCollection_5' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_5'
                ],
            ],
            'Versioniser_ProductCollection_6' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_6'
                ],
            ],
            'Versioniser_ProductCollection_7' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_7'
                ],
            ],
            'Versioniser_AliasSettingsCollection_1' => [
                'parameter' => [
                    'aliasVersioniser1' => 'Versioniser_AliasSettingsEntity_1'
                ],
            ],
            'Versioniser_AliasSettingsCollection_2' => [
                'parameter' => [
                    'aliasVersioniser2' => 'Versioniser_AliasSettingsEntity_2'
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
            'Versioniser_InvoiceSettingsCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_InvoiceSettings_2'
                ],
            ],
        ],
    ]
];
