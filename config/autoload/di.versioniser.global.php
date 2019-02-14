<?php
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Slim\Versioning\AliasSettingsCollection;
use CG\Slim\Versioning\AliasSettingsEntity;
use CG\Slim\Versioning\CategoryCollection;
use CG\Slim\Versioning\CategoryEntity;
use CG\Slim\Versioning\CategoryTemplateCollection;
use CG\Slim\Versioning\CategoryTemplateEntity;
use CG\Slim\Versioning\InvoiceMappingCollection;
use CG\Slim\Versioning\InvoiceMappingEntity;
use CG\Slim\Versioning\InvoiceSettingsCollection;
use CG\Slim\Versioning\InvoiceSettingsEntity;
use CG\Slim\Versioning\ListingCollection;
use CG\Slim\Versioning\ListingEntity;
use CG\Slim\Versioning\ListingStatusHistoryCollection;
use CG\Slim\Versioning\ListingStatusHistoryEntity;
use CG\Slim\Versioning\LocationCollection;
use CG\Slim\Versioning\LocationEntity;
use CG\Slim\Versioning\OrderCollection;
use CG\Slim\Versioning\OrderEntity;
use CG\Slim\Versioning\OrderItemCollection;
use CG\Slim\Versioning\OrderItemEntity;
use CG\Slim\Versioning\OrderLabelCollection;
use CG\Slim\Versioning\OrderLabelEntity;
use CG\Slim\Versioning\PickListSettingsCollection;
use CG\Slim\Versioning\PickListSettingsEntity;
use CG\Slim\Versioning\ProductCollection;
use CG\Slim\Versioning\ProductDetailCollection;
use CG\Slim\Versioning\ProductDetailEntity;
use CG\Slim\Versioning\ProductEntity;
use CG\Slim\Versioning\ProductSettingsCollection;
use CG\Slim\Versioning\ProductSettingsEntity;
use CG\Slim\Versioning\StockCollection;
use CG\Slim\Versioning\StockEntity;
use CG\Slim\Versioning\StockLocationCollection;
use CG\Slim\Versioning\StockLocationEntity;
use CG\Slim\Versioning\StockLogCollection;
use CG\Slim\Versioning\TemplateCollection;
use CG\Slim\Versioning\TemplateEntity;
use CG\Slim\Versioning\TrackingCollection;
use CG\Slim\Versioning\TrackingEntity;
use CG\Slim\Versioning\UnimportedListingCollection;
use CG\Slim\Versioning\UnimportedListingEntity;
use CG\Slim\Versioning\UnimportedListingMarketplace;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'Versioniser_ListingCollection_1' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_1' => ListingEntity\Versioniser1::class,
                'Versioniser_ListingCollection_2' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_2' => ListingEntity\Versioniser2::class,
                'Versioniser_ListingCollection_3' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_3' => ListingEntity\Versioniser3::class,
                'Versioniser_ListingCollection_4' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_4' => ListingEntity\Versioniser4::class,
                'Versioniser_ListingCollection_5' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_5' => ListingEntity\Versioniser5::class,
                'Versioniser_ListingCollection_6' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_6' => ListingEntity\Versioniser6::class,
                'Versioniser_ListingCollection_7' => ListingCollection\Versioniser::class,
                'Versioniser_ListingEntity_7' => ListingEntity\Versioniser7::class,
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
                'Versioniser_OrderCollection_9' => OrderCollection\Versioniser9::class,
                'Versioniser_OrderEntity_9' => OrderEntity\Versioniser9::class,
                'Versioniser_OrderCollection_10' => OrderCollection\Versioniser10::class,
                'Versioniser_OrderEntity_10' => OrderEntity\Versioniser10::class,
                'Versioniser_OrderCollection_11' => OrderCollection\Versioniser11::class,
                'Versioniser_OrderEntity_11' => OrderEntity\Versioniser11::class,
                'Versioniser_OrderCollection_12' => OrderCollection\Versioniser12::class,
                'Versioniser_OrderEntity_12' => OrderEntity\Versioniser12::class,
                'Versioniser_OrderCollection_13' => OrderCollection\Versioniser13::class,
                'Versioniser_OrderEntity_13' => OrderEntity\Versioniser13::class,
                'Versioniser_OrderCollection_14' => OrderCollection\Versioniser14::class,
                'Versioniser_OrderEntity_14' => OrderEntity\Versioniser14::class,
                'Versioniser_OrderCollection_15' => OrderCollection\Versioniser15::class,
                'Versioniser_OrderEntity_15' => OrderEntity\Versioniser15::class,
                'Versioniser_OrderCollection_16' => OrderCollection\Versioniser16::class,
                'Versioniser_OrderEntity_16' => OrderEntity\Versioniser16::class,
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
                'Versioniser_OrderItemCollection_9' => OrderItemCollection\Versioniser9::class,
                'Versioniser_OrderItemEntity_9' => OrderItemEntity\Versioniser9::class,
                'Versioniser_OrderLabelCollection_1' => OrderLabelCollection\Versioniser1::class,
                'Versioniser_OrderLabelEntity_1' => OrderLabelEntity\Versioniser1::class,
                'Versioniser_OrderLabelCollection_2' => OrderLabelCollection\Versioniser2::class,
                'Versioniser_OrderLabelEntity_2' => OrderLabelEntity\Versioniser2::class,
                'Versioniser_OrderLabelCollection_3' => OrderLabelCollection\Versioniser3::class,
                'Versioniser_OrderLabelEntity_3' => OrderLabelEntity\Versioniser3::class,
                'Versioniser_OrderLabelCollection_4' => OrderLabelCollection\Versioniser4::class,
                'Versioniser_OrderLabelEntity_4' => OrderLabelEntity\Versioniser4::class,
                'Versioniser_OrderLabelCollection_5' => OrderLabelCollection\Versioniser5::class,
                'Versioniser_OrderLabelEntity_5' => OrderLabelEntity\Versioniser5::class,
                'Versioniser_TemplateCollection_1' => TemplateCollection\Versioniser1::class,
                'Versioniser_TemplateEntity_1' => TemplateEntity\Versioniser1::class,
                'Versioniser_ProductDetailCollection_1' => ProductDetailCollection\Versioniser1::class,
                'Versioniser_ProductDetailEntity_1' => ProductDetailEntity\Versioniser1::class,
                'Versioniser_ProductDetailCollection_2' => ProductDetailCollection\Versioniser2::class,
                'Versioniser_ProductDetailEntity_2' => ProductDetailEntity\Versioniser2::class,
                'Versioniser_ProductDetailCollection_3' => ProductDetailCollection\Versioniser3::class,
                'Versioniser_ProductDetailEntity_3' => ProductDetailEntity\Versioniser3::class,
                'Versioniser_ProductDetailCollection_4' => ProductDetailCollection\Versioniser4::class,
                'Versioniser_ProductDetailEntity_4' => ProductDetailEntity\Versioniser4::class,
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
                'Versioniser_ProductCollection_8' => ProductCollection\Versioniser8::class,
                'Versioniser_ProductEntity_8' => ProductEntity\Versioniser8::class,
                'Versioniser_ProductCollection_9' => ProductCollection\Versioniser9::class,
                'Versioniser_ProductEntity_9' => ProductEntity\Versioniser9::class,
                'Versioniser_ProductCollection_10' => ProductCollection\Versioniser10::class,
                'Versioniser_ProductEntity_10' => ProductEntity\Versioniser10::class,
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
                'Versioniser_InvoiceSettingsCollection_3' => InvoiceSettingsCollection\Versioniser3::class,
                'Versioniser_InvoiceSettings_3' => InvoiceSettingsEntity\Versioniser3::class,
                'Versioniser_InvoiceSettingsCollection_4' => InvoiceSettingsCollection\Versioniser4::class,
                'Versioniser_InvoiceSettings_4' => InvoiceSettingsEntity\Versioniser4::class,
                'Versioniser_InvoiceSettingsCollection_5' => InvoiceSettingsCollection\Versioniser5::class,
                'Versioniser_InvoiceSettings_5' => InvoiceSettingsEntity\Versioniser5::class,
                'Versioniser_InvoiceSettingsCollection_6' => InvoiceSettingsCollection\Versioniser6::class,
                'Versioniser_InvoiceSettings_6' => InvoiceSettingsEntity\Versioniser6::class,
                'Versioniser_InvoiceSettingsCollection_7' => InvoiceSettingsCollection\Versioniser7::class,
                'Versioniser_InvoiceSettings_7' => InvoiceSettingsEntity\Versioniser7::class,
                'Versioniser_InvoiceSettingsCollection_8' => InvoiceSettingsCollection\Versioniser8::class,
                'Versioniser_InvoiceSettings_8' => InvoiceSettingsEntity\Versioniser8::class,
                'Versioniser_InvoiceSettingsCollection_9' => InvoiceSettingsCollection\Versioniser9::class,
                'Versioniser_InvoiceSettings_9' => InvoiceSettingsEntity\Versioniser9::class,
                'Versioniser_InvoiceSettingsCollection_10' => InvoiceSettingsCollection\Versioniser10::class,
                'Versioniser_InvoiceSettings_10' => InvoiceSettingsEntity\Versioniser10::class,
                'Versioniser_ListingStatusHistoryCollection_1' => ListingStatusHistoryCollection\Versioniser1::class,
                'Versioniser_ListingStatusHistoryEntity_1' => ListingStatusHistoryEntity\Versioniser1::class,
                'Versioniser_LocationCollection_1' => LocationCollection\Versioniser1::class,
                'Versioniser_LocationEntity_1' => LocationEntity\Versioniser1::class,
                'Versioniser_TrackingCollection_1' => TrackingCollection\Versioniser1::class,
                'Versioniser_OrderTrackingCollection_1' => TrackingCollection\Versioniser1::class,
                'Versioniser_OrderTrackingEntity_1' => TrackingEntity\Versioniser1::class,
                'Versioniser_StockCollection_1' => StockCollection\Versioniser::class,
                'Versioniser_StockEntity_1' => StockEntity\Versioniser1::class,
                'Versioniser_StockCollection_2' => StockCollection\Versioniser2::class,
                'Versioniser_StockEntity_2' => StockEntity\Versioniser2::class,
                'Versioniser_UnimportedListingMarketplace_1' => UnimportedListingMarketplace\Versioniser1::class,
                'Versioniser_CategoryCollection_1' => CategoryCollection\Versioniser1::class,
                'Versioniser_CategoryEntity_1' => CategoryEntity\Versioniser1::class,
                'Versioniser_CategoryTemplateCollection_1' => CategoryTemplateCollection\Versioniser1::class,
                'Versioniser_CategoryTemplateEntity_1' => CategoryTemplateEntity\Versioniser1::class,
                'Versioniser_InvoiceMappingEntity_1' => InvoiceMappingEntity\Versioniser1::class,
                'Versioniser_InvoiceMappingCollection_1' => InvoiceMappingCollection\Versioniser1::class,
                'Versioniser_StockLogCollection_1' => StockLogCollection\Versioniser1::class,
                'Versioniser_PickListSettingsEntity_1' => PickListSettingsEntity\Versioniser1::class,
                'Versioniser_PickListSettingsCollection_1' => PickListSettingsCollection\Versioniser1::class,
                'Versioniser_ProductSettingsEntity_1' => ProductSettingsEntity\Versioniser1::class,
                'Versioniser_ProductSettingsCollection_1' => ProductSettingsCollection\Versioniser1::class,
                'Versioniser_StockLocationCollection_1' => StockLocationCollection\Versioniser1::class,
                'Versioniser_StockLocationEntity_1' => StockLocationEntity\Versioniser1::class,
            ],
            'Versioniser_ListingCollection_1' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_1',
                ],
            ],
            'Versioniser_ListingCollection_2' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_2',
                ],
            ],
            'Versioniser_ListingCollection_3' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_3',
                ],
            ],
            'Versioniser_ListingCollection_4' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_4',
                ],
            ],
            'Versioniser_ListingCollection_5' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_5',
                ],
            ],
            'Versioniser_ListingCollection_6' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_6',
                ],
            ],
            'Versioniser_ListingCollection_7' => [
                'parameter' => [
                    'entityVersioniser' => 'Versioniser_ListingEntity_7',
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
            'Versioniser_OrderCollection_9' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_9',
                ],
            ],
            'Versioniser_OrderCollection_10' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_10',
                ],
            ],
            'Versioniser_OrderCollection_11' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderEntity_11',
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
            'Versioniser_OrderLabelCollection_2' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_OrderLabelEntity_2',
                ],
            ],
            'Versioniser_TemplateCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_TemplateEntity_1'
                ],
            ],
            'Versioniser_ProductDetailCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductDetailEntity_1'
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
            'Versioniser_ProductCollection_8' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_8'
                ],
            ],
            'Versioniser_ProductCollection_9' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_9'
                ],
            ],
            'Versioniser_ProductCollection_10' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_ProductEntity_10'
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
            'Versioniser_StockCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_StockEntity_1',
                ],
            ],
            'Versioniser_InvoiceMappingCollection_1' => [
                'parameter' => [
                    'entityVersioner' => 'Versioniser_InvoiceMappingEntity_1',
                ]
            ]
        ],
    ]
];
