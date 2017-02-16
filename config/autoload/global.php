<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use CG\ETag\Storage\Predis;
use CG\ETag\StorageInterface;
use Zend\Db\Sql\Sql;
use CG\Zend\Stdlib\Db\Sql\Sql as CGSql;
use CG\Cache\Client\Redis as CacheRedis;
use CG\ETag\Storage\Predis as EtagRedis;

use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager as CGEventManager;
use CG\Cache\IncrementorInterface;
use CG\Cache\Increment\Incrementor;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitApi;
use Slim\Slim;

// Account
use CG\Account\Client\Service as AccountService;
use CG\Account\Shared\Repository as AccountRepository;
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Account\Service\Storage\Db as AccountPersistentStorage;
use CG\Account\Shared\Mapper as AccountMapper;
use CG\Channel\ShippingChannelsProviderInterface as ChannelShippingChannelsProviderInterface;
use CG\Dataplug\Carriers as DataplugCarriers;

//Polling Window
use CG\Account\Service\PollingWindow\Service as AccountPollingWindowService;
use CG\Account\Service\PollingWindow\Storage\Db as AccountPollingWindowDbStorage;
use CG\Account\Shared\PollingWindow\Mapper as AccountPollingWindowMapper;
use CG\Account\Shared\PollingWindow\StorageInterface as AccountPollingWindowStorage;
use CG\Account\Client\PollingWindow\Storage\Api as AccountPollingWindowApiStorage;

//Order
use CG\Order\Shared\Entity as OrderEntity;
use CG\Order\Service\Service as OrderService;
use CG\Order\Locking\Service as OrderLockingService;
use CG\Order\Shared\Repository as OrderRepository;
use CG\Order\Shared\StorageInterface as OrderStorage;
use CG\Order\Service\Storage\Cache as OrderCacheStorage;
use CG\Order\Service\Storage\Persistent as OrderPersistentStorage;
use CG\Order\Service\Storage\Persistent\Db as OrderPersistentDbStorage;
use CG\Order\Service\Storage\ElasticSearch as OrderElasticSearchStorage;
use CG\Order\Client\Storage\Api as OrderApiStorage;
use CG\Order\Client\StorageInterface as OrderClientStorage;
use CG\SequentialNumbering\ProviderInterface as SequentialNumberingProviderInterface;
use CG\SequentialNumbering\Provider\Redis as SequentialNumberingProviderRedis;
use CG\Order\Shared\InvoiceEmailer\Service as InvoiceEmailerService;

//Note
use CG\Order\Shared\Note\Entity as NoteEntity;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Repository as NoteRepository;
use CG\Order\Service\Note\Storage\Cache as NoteCacheStorage;
use CG\Order\Service\Note\Storage\Db as NoteDbStorage;

//Tracking
use CG\Order\Shared\Tracking\Entity as TrackingEntity;
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Shared\Tracking\Repository as TrackingRepository;
use CG\Order\Service\Tracking\Storage\Cache as TrackingCacheStorage;
use CG\Order\Service\Tracking\Storage\Db as TrackingDbStorage;

//Alert
use CG\Order\Shared\Alert\Entity as AlertEntity;
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Shared\Alert\Repository as AlertRepository;
use CG\Order\Service\Alert\Storage\Cache as AlertCacheStorage;
use CG\Order\Service\Alert\Storage\Db as AlertDbStorage;

//Item
use CG\Order\Shared\Item\Entity as ItemEntity;
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Service\Item\InvalidationService as ItemInvalidationService;
use CG\Order\Locking\Item\Service as ItemLockingService;
use CG\Order\Shared\Item\Repository as ItemRepository;
use CG\Order\Service\Item\Storage\Cache as ItemCacheStorage;
use CG\Order\Shared\Item\StorageInterface as ItemStorageInterface;
use CG\Order\Service\Item\Storage\Persistent\Db as ItemPersistentDbStorage;
use CG\Order\Service\Item\Transaction\UpdateItemAndStockFactory as UpdateItemAndStockTransactionFactory;

//Fee
use CG\Order\Service\Item\Fee\Service as FeeService;
use CG\Order\Shared\Item\Fee\Repository as FeeRepository;
use CG\Order\Service\Item\Fee\Storage\Cache as FeeCacheStorage;
use CG\Order\Service\Item\Fee\Storage\Db as FeeDbStorage;

//GiftWrap
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
use CG\Order\Shared\Item\GiftWrap\Repository as GiftWrapRepository;
use CG\Order\Service\Item\GiftWrap\Storage\Cache as GiftWrapCacheStorage;
use CG\Order\Service\Item\GiftWrap\Storage\Db as GiftWrapDbStorage;

//UserChange
use CG\Order\Shared\UserChange\Entity as UserChangeEntity;
use CG\Order\Shared\UserChange\Mapper as UserChangeMapper;
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Shared\UserChange\Repository as UserChangeRepository;
use CG\Order\Service\UserChange\Storage\Cache as UserChangeCacheStorage;
use CG\Order\Service\UserChange\Storage\MongoDb as UserChangeMongoDbStorage;
use CG\Order\Service\UserChange\Storage\Db as UserChangeDbStorage;

//Batch
use CG\Order\Service\Batch\Service as BatchService;
use CG\Order\Shared\Batch\Repository as BatchRepository;
use CG\Order\Service\Batch\Storage\Cache as BatchCacheStorage;
use CG\Order\Service\Batch\Storage\Db as BatchDbStorage;
use CG\Order\Shared\Batch\Mapper as BatchMapper;

//UserPreference
use CG\UserPreference\Service\Service as UserPreferenceService;
use CG\UserPreference\Shared\Repository as UserPreferenceRepository;
use CG\UserPreference\Service\Storage\Cache as UserPreferenceCacheStorage;
use CG\UserPreference\Service\Storage\MongoDb as UserPreferenceMongoDbStorage;

//Tag
use CG\Order\Service\Tag\Service as TagService;
use CG\Order\Shared\Tag\Repository as TagRepository;
use CG\Order\Service\Tag\Storage\Cache as TagCacheStorage;
use CG\Order\Service\Tag\Storage\Db as TagDbStorage;
use CG\Order\Shared\Tag\Mapper as TagMapper;

// Label
use CG\Order\Shared\Label\Mapper as LabelMapper;
use CG\Order\Shared\Label\StorageInterface as LabelStorage;
use CG\Order\Shared\Label\Repository as LabelRepository;
use CG\Order\Service\Label\Storage\Cache as LabelCacheStorage;
use CG\Order\Service\Label\Storage\MongoDb as LabelMongoDbStorage;
use CG\Order\Service\Label\Storage\MetaPlusLabelData as LabelMetaPlusLabelDataStorage;
use CG\Order\Service\Label\Storage\LabelData\S3 as LabelLabelDataS3Storage;
use CG\Order\Service\Label\Storage\MetaData\Db as LabelMetaDataDbStorage;

//Cilex Command
use CG\Channel\Command\Order\Download as OrderDownloadCommand;
use CG\Channel\Command\Order\Generator as OrderGeneratorCommand;
use CG\Channel\Command\Order\Generator\SimpleOrderFactory;
use CG\Channel\Command\Service as AccountCommandService;
use CG\Ekm\Gearman\Generator\OrderDownload as EkmOrderUpdateGenerator;
use CG\Order\Shared\Command\ApplyMissingStockAdjustmentsForCancDispRefOrders as ApplyMissingStockAdjustmentsForCancDispRefOrdersCommand;
use CG\Order\Shared\Command\UpdateAllItemsTax as UpdateAllItemsTaxCommand;
use CG\Order\Shared\Command\UpdateAllItemsImages as UpdateAllItemsImagesCommand;
use CG\Order\Shared\Command\CorrectStockOfItemsWithIncorrectStockManagedFlag as CorrectStockOfItemsWithIncorrectStockManagedFlagCommand;
use CG\Order\Shared\Command\ReSyncOrderCounts as ReSyncOrderCountsCommand;
use CG\Stock\Command\ZeroNegativeStock as ZeroNegativeStockCommand;
use CG\CGLib\Command\EnsureProductsAndListingsAssociatedWithRootOu as EnsureProductsAndListingsAssociatedWithRootOuCommand;
use CG\Listing\Command\CorrectPendingListingsStatusFromSiblingListings as CorrectPendingListingsStatusFromSiblingListingsCommand;
use CG\Listing\Command\AddSkusToListings as AddSkusToListingsCommand;
use CG\Listing\Command\DeleteAlreadyImportedUnimportedListings as DeleteAlreadyImportedUnimportedListingsCommand;
use CG\Stock\Command\CreateMissingStock as CreateMissingStockCommand;
use CG\Stock\Command\RemoveDuplicateStock as RemoveDuplicateStockCommand;
use CG\Order\Shared\Command\AutoArchiveOrders as AutoArchiveOrdersCommand;
use CG\Stock\Command\FindIncorrectlyAllocatedStock as FindIncorrectlyAllocatedStockCommand;

//Filter
use CG\Order\Service\Filter\Service as FilterService;
use CG\Order\Service\Filter\Storage\Cache as FilterCache;
use CG\Order\Service\Filter\Entity\Storage\Cache as FilterEntityCacheStorage;
use CG\Order\Service\Filter\Entity\StorageInterface as FilterEntityStorage;

//Template
use CG\Template\Service as TemplateService;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Storage\Cache as TemplateCacheStorage;
use CG\Template\Storage\MongoDb as TemplateMongoDbStorage;

//Cancel
use CG\Order\Service\Cancel\Storage\Db as CancelDbStorage;

//Shipping
use CG\Order\Shared\Shipping\Method\Entity as ShippingMethod;
use CG\Order\Shared\Shipping\Method\Mapper as ShippingMethodMapper;
use CG\Order\Shared\Shipping\Method\Repository as ShippingMethodRepository;
use CG\Order\Service\Shipping\Method\Service as ShippingMethodService;
use CG\Order\Service\Shipping\Method\Storage\Db as ShippingMethodDbStorage;
use CG\Order\Service\Shipping\Method\Storage\Cache as ShippingMethodCacheStorage;

// Invoice Settings
use CG\Settings\Invoice\Service\Service as InvoiceSettingsService;
use CG\Settings\Invoice\Service\Storage\Cache as InvoiceSettingsCacheStorage;
use CG\Settings\Invoice\Service\Storage\MongoDb as InvoiceSettingsMongoDbStorage;
use CG\Settings\Invoice\Shared\Repository as InvoiceSettingsRepository;

// Alias Settings
use CG\Settings\Shipping\Alias\Service as AliasSettingsService;
use CG\Settings\Shipping\Alias\Mapper as AliasSettingsMapper;
use CG\Settings\Shipping\Alias\Storage\Cache as AliasSettingsCacheStorage;
use CG\Settings\Shipping\Alias\Storage\Db as AliasSettingsDbStorage;
use CG\Settings\Shipping\Alias\Repository as AliasSettingsRepository;

//Usage
use CG\Usage\Storage\Db as UsageDb;
use CG\Usage\Aggregate\Storage\Db as UsageAggregateDb;
use CG\Usage\Storage\Redis as UsageRedis;
use CG\Usage\Repository as UsageRepository;
use CG\Usage\StorageInterface as UsageStorageInterface;

// Product
use CG\Product\Entity as ProductEntity;
use CG\Product\Service\Service as ProductService;
use CG\Product\Client\Service as ProductClientService;
use CG\Product\Repository as ProductRepository;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Storage\Db as ProductDbStorage;
use CG\Product\Storage\Cache as ProductCacheStorage;
use CG\Product\StorageInterface as ProductStorage;
use CG\Order\Client\Gearman\Workload\UpdateItemsTaxFactory as UpdateItemsTaxWorkloadFactory;
use CG\Order\Client\Gearman\Workload\UpdateItemsImagesFactory as UpdateItemsImagesWorkloadFactory;
use CG\Product\Locking\Entity as LockingProduct;

// ProductDetail
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\Repository as ProductDetailRepository;
use CG\Product\Detail\Storage\Cache as ProductDetailCacheStorage;
use CG\Product\Detail\Storage\Db as ProductDetailDbStorage;
use CG\Product\Detail\StorageInterface as ProductDetailStorage;

// Transaction
use CG\Transaction\ClientInterface as TransactionClientInterface;
use CG\Transaction\LockInterface as LockClientInterface;
use CG\Transaction\Client\Redis as TransactionRedisClient;

// Stock
use CG\Stock\Entity as StockEntity;
use CG\Stock\AdjustmentCalculator as StockAdjustmentCalculator;
use CG\Stock\Service as StockService;
use CG\Stock\Repository as StockRepository;
use CG\Stock\Storage\Cache as StockCacheStorage;
use CG\Stock\Storage\Db as StockDbStorage;
use CG\Stock\Storage\Api as StockApiStorage;
use CG\Stock\StorageInterface as StockStorage;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Location\Entity as StockLocationEntity;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Location\Repository as StockLocationRepository;
use CG\Stock\Location\Storage\Cache as StockLocationCacheStorage;
use CG\Stock\Location\Storage\Db as StockLocationDbStorage;
use CG\Stock\Location\Storage\Api as StockLocationApiStorage;
use CG\Stock\Location\StorageInterface as StockLocationStorage;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Command\Adjustment as StockAdjustmentCommand;
use CG\Stock\Locking\Creator as StockCreator;
use CG\Stock\Locking\Entity as LockingStock;
use CG\Stock\Location\Service\Service as StockLocationServiceService;
use CG\Controllers\Stock\Location\Location as StockLocationController;
use CG\Controllers\Stock\Location\Location\Collection as StockLocationCollectionController;

// StockLog
use CG\Stock\Audit\Combined\Mapper as StockLogMapper;
use CG\Stock\Audit\Combined\Repository as StockLogRepository;
use CG\Stock\Audit\Combined\Storage\Cache as StockLogCacheStorage;
use CG\Stock\Audit\Combined\Storage\Db as StockLogDbStorage;
use CG\Stock\Audit\Combined\StorageInterface as StockLogStorage;

// Order Counts
use CG\Order\Shared\OrderCounts\Repository as OrderCountsRepository;
use CG\Order\Shared\OrderCounts\Mapper as OrderCountsMapper;
use CG\Order\Shared\OrderCounts\Storage\Redis as OrderCountsRedisStorage;
use CG\Order\Shared\OrderCounts\Storage\Api as OrderCountsApiStorage;
use CG\Order\Shared\OrderCounts\StorageInterface as OrderCountsStorage;

// Listing
use CG\Listing\Entity as ListingEntity;
use CG\Listing\Service as ListingDeprService;
use CG\Listing\Client\Service as ListingClientService;
use CG\Listing\Service\Service as ListingService;
use CG\Listing\Repository as ListingRepository;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Storage\Db as ListingDbStorage;
use CG\Listing\Storage\Cache as ListingCacheStorage;
use CG\Listing\StorageInterface as ListingStorage;

// Listing Status History
use CG\Listing\StatusHistory\StorageInterface as ListingStatusHistoryStorage;
use CG\Listing\StatusHistory\Repository as ListingStatusHistoryRepository;
use CG\Listing\StatusHistory\Storage\Cache as ListingStatusHistoryCacheStorage;
use CG\Listing\StatusHistory\Storage\Db as ListingStatusHistoryDbStorage;

// Unimported Listing
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Unimported\Repository as UnimportedListingRepository;
use CG\Listing\Unimported\Mapper as UnimportedListingMapper;
use CG\Listing\Unimported\Storage\Db as UnimportedListingDbStorage;
use CG\Listing\Unimported\Storage\Cache as UnimportedListingCacheStorage;
use CG\Listing\Unimported\Storage\Api as UnimportedListingApi;
use CG\Listing\Unimported\StorageInterface as UnimportedListingStorage;

// Unimported Listing Marketplace
use CG\Listing\Unimported\Marketplace\StorageInterface as UnimportedListingMarketplaceStorage;
use CG\Listing\Unimported\Marketplace\Repository as UnimportedListingMarketplaceRepository;
use CG\Listing\Unimported\Marketplace\Storage\Cache as UnimportedListingMarketplaceCacheStorage;
use CG\Listing\Unimported\Marketplace\Storage\Db as UnimportedListingMarketplaceDbStorage;

// Image
use CG\Image\Entity as ImageEntity;
use CG\Image\Service as ImageService;
use CG\Image\Storage\Api as ImageApi;

// Location
use CG\Location\Service as LocationService;
use CG\Location\Repository as LocationRepository;
use CG\Location\Mapper as LocationMapper;
use CG\Location\Storage\Db as LocationDbStorage;
use CG\Location\Storage\Cache as LocationCacheStorage;
use CG\Location\StorageInterface as LocationStorage;

// Caching
use CG\Cache\InvalidationHandler;

// PickList
use CG\Settings\PickList\Service as PickListService;
use CG\Settings\PickList\Repository as PickListRepository;
use CG\Settings\PickList\Mapper as PickListMapper;
use CG\Settings\PickList\Storage\Cache as PickListCacheStorage;
use CG\Settings\PickList\Storage\Db as PickListDbStorage;

// Logging
use CG\Log\Shared\Storage\Redis\Channel as RedisChannel;

use Symfony\Component\Console\Output\Output as SymfonyOutput;
use CG\Product\Command\RemoveThenCorrectImportedProducts;

// Product/VariationAttributeMap
use CG\Product\VariationAttributeMap\Service as VariationAttributeMapService;
use CG\Product\VariationAttributeMap\Mapper as VariationAttributeMapMapper;
use CG\Product\VariationAttributeMap\Storage\Db as VariationAttributeMapDbStorage;

// Phantom JS
use JonnyW\PhantomJs\Client as PhantomJSClient;

// EKM
use CG\Ekm\Product\TaxRate\Mapper as EkmTaxRateMapper;
use CG\Ekm\Product\TaxRate\Repository as EkmTaxRateRepository;
use CG\Ekm\Product\TaxRate\Service as EkmTaxRateService;
use CG\Ekm\Product\TaxRate\Storage\Cache as EkmTaxRateCache;
use CG\Ekm\Product\TaxRate\Storage\Db as EkmTaxRateDb;
use CG\Ekm\Product\TaxRate\StorageInterface as EkmTaxRateStorage;

// Api Settings
use CG\Settings\Api\StorageInterface as ApiSettingsStorage;
use CG\Settings\Api\Repository as ApiSettingsRepository;
use CG\Settings\Api\Storage\Cache as ApiSettingsCacheStorage;
use CG\Settings\Api\Storage\Db as ApiSettingsDbStorage;

// Notifications
use CG\Notification\Queue as NotificationQueue;

// WooCommerce
use CG\WooCommerce\ListingImport as WooCommerceListingImport;

// Product Settings
use CG\Settings\Product\StorageInterface as ProductSettingsStorage;
use CG\Settings\Product\Repository as ProductettingsRepository;
use CG\Settings\Product\Storage\Cache as ProductSettingsCacheStorage;
use CG\Settings\Product\Storage\Db as ProductSettingsDbStorage;

// Locking
use CG\Locking\StorageInterface as LockingStorage;
use CG\Redis\Locking\Storage as LockingRedisStorage;

// Customer Order Counts
use CG\Order\Shared\CustomerCounts\StorageInterface as CustomerCountStorage;
use CG\Order\Shared\CustomerCounts\Repository as CustomerCountRepository;
use CG\Order\Shared\CustomerCounts\Storage\Cache as CustomerCountCacheStorage;
use CG\Order\Shared\CustomerCounts\Storage\OrderLookup as CustomerCountOrderLookupStorage;

// Amazon Logistics
use CG\Amazon\ShippingService\StorageInterface as AmazonShippingServiceStorage;
use CG\Amazon\ShippingService\Storage\Cache as AmazonShippingServiceCacheStorage;
use CG\Amazon\ShippingService\Storage\Db as AmazonShippingServiceDbStorage;
use CG\Amazon\ShippingService\Repository as AmazonShippingServiceRepository;
use CG\Amazon\ShippingService\Service as AmazonShippingServiceService;

use CG\Account\Command\Sales\Disable as SalesAccountDisable;

// SetupProgress Settings
use CG\Settings\SetupProgress\Mapper as SetupProgressSettingsMapper;
use CG\Settings\SetupProgress\Repository as SetupProgressSettingsRepository;
use CG\Settings\SetupProgress\Storage\Cache as SetupProgressSettingsCacheStorage;
use CG\Settings\SetupProgress\Storage\Db as SetupProgressSettingsDbStorage;
use CG\Settings\SetupProgress\StorageInterface as SetupProgressSettingsStorage;

// ExchangeRate
use CG\ExchangeRate\Service as ExchangeRateService;
use CG\ExchangeRate\Repository as ExchangeRateRepository;
use CG\ExchangeRate\Mapper as ExchangeRateMapper;
use CG\ExchangeRate\Storage\Db as ExchangeRateDbStorage;
use CG\ExchangeRate\Storage\Cache as ExchangeRateCacheStorage;
use CG\ExchangeRate\Storage\ExternalApi as ExchangeRateExternalApiStorage;

$config = array(
    'di' => array(
        'definition' => [
            'class' => [
                PhantomJSClient::class => [
                    'instantiator' => 'JonnyW\PhantomJs\Client::getInstance',
                    'methods' => [
                        'addOption' => [
                            'option' => [
                                'required' => true
                            ]
                        ],
                        'setPhantomJs' => [
                            'path' => [
                                'required' => true
                            ]
                        ],
                        'setPhantomLoader' => [
                            'path' => [
                                'required' => true
                            ]
                        ]
                    ]
                ]
            ]
        ],
        'instance' => array(
            'aliases' => array(
                'ReadCGSql' => CGSql::class,
                'FastReadCGSql' => CGSql::class,
                'WriteCGSql' => CGSql::class,
                'amazonReadCGSql' => CGSql::class,
                'amazonFastReadCGSql' => CGSql::class,
                'amazonWriteCGSql' => CGSql::class,
                'EkmOrderDownloadCommand' => OrderDownloadCommand::class,
                'LiveOrderPersistentDbStorage' => OrderPersistentDbStorage::class,
                'StockApiService' => StockService::class,
                'StockLocationApiService' => StockLocationService::class,
                'ExchangeRateRepositoryPrimary' => ExchangeRateRepository::class,
                'ExchangeRateRepositorySecondary' => ExchangeRateRepository::class,
            ),
            'ReadCGSql' => array(
                'parameter' => array(
                    'adapter' => 'Read'
                )
            ),
            'FastReadCGSql' => array(
                'parameter' => array(
                    'adapter' => 'FastRead'
                )
            ),
            'WriteCGSql' => array(
                'parameter' => array(
                    'adapter' => 'Write'
                )
            ),
            InvalidationHandler::class => [
                'parameters' => [
                    'relationships' => [
                        StockLocationEntity::class => [
                            [
                                'entityClass' => StockEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY
                            ]
                        ],
                        StockEntity::class => [
                            ['entityClass' => StockLocationEntity::class]
                        ],
                        ProductEntity::class => [
                            [
                                'entityClass' => StockEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_EMBED_ENTITY
                            ],
                            ['entityClass' => ListingEntity::class],
                            ['entityClass' => ImageEntity::class]
                        ],

                        ItemEntity::class => [
                            [
                                'entityClass' => OrderEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY,
                                'getter' => 'getOrderId'
                            ]
                        ],
                        NoteEntity::class => [
                            [
                                'entityClass' => OrderEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY,
                                'getter' => 'getOrderId'
                            ]
                        ],
                        AlertEntity::class => [
                            [
                                'entityClass' => OrderEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY,
                                'getter' => 'getOrderId'
                            ]
                        ],
                        TrackingEntity::class => [
                            [
                                'entityClass' => OrderEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY,
                                'getter' => 'getOrderId'
                            ]
                        ],
                        UserChangeEntity::class => [
                            [
                                'entityClass' => OrderEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_PARENT_ENTITY,
                                'getter' => 'getOrderId'
                            ]
                        ],
                        OrderEntity::class => [
                            ['entityClass' => ItemEntity::class],
                            ['entityClass' => NoteEntity::class],
                            ['entityClass' => AlertEntity::class],
                            ['entityClass' => TrackingEntity::class],
                            [
                                'entityClass' => UserChangeEntity::class,
                                'type' => InvalidationHandler::RELATION_TYPE_EMBED_ENTITY
                            ],
                        ]
                    ],
                    'debugCachable' => [
                    ],
                ]
            ],
            AccountService::class => array(
                'parameters' => array(
                    'repository' => AccountApiStorage::class,
                )
            ),
            AccountPollingWindowService::class => array(
                'parameter' => array(
                    'repository' => AccountPollingWindowDbStorage::class
                )
            ),
            AccountPollingWindowDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => AccountPollingWindowMapper::class
                )
            ),
            CacheRedis::class => array(
                'parameter' => array(
                    'predis' => 'unreliable_redis'
                )
            ),
            EtagRedis::class => array(
                'parameter' => array(
                    'predisClient' => 'unreliable_redis'
                )
            ),
            OrderRepository::class => array(
                'parameter' => array(
                    'storage' => OrderCacheStorage::class,
                    'repository' => OrderPersistentStorage::class
                )
            ),
            OrderService::class => array(
                'parameters' => array(
                    'repository' => OrderPersistentStorage::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'filterStorage' => FilterCache::class
                )
            ),
            OrderLockingService::class => array(
                'parameters' => array(
                    'repository' => OrderPersistentStorage::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'filterStorage' => FilterCache::class
                )
            ),
            OrderPersistentDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadMysqli',
                    'fastReadSql' => 'FastReadMysqli',
                    'writeSql' => 'WriteMysqli'
                )
            ),
            'LiveOrderPersistentDbStorage' => [
                'parameter' => [
                    'orderTableName' => OrderPersistentDbStorage::ORDER_TABLE_LIVE_NAME
                ]
            ],
            OrderPersistentStorage::class => ['ReadCGSql' => CGSql::class,
                'FastReadCGSql' => CGSql::class,
                'WriteCGSql' => CGSql::class,
                'parameter' => [
                    'sqlClient' => OrderPersistentDbStorage::class,
                    'liveSqlClient' => 'LiveOrderPersistentDbStorage',
                ]
            ],
            NoteService::class => array(
                'parameters' => array(
                    'repository' => NoteDbStorage::class
                )
            ),
            NoteRepository::class => array(
                'parameter' => array(
                    'storage' => NoteCacheStorage::class,
                    'repository' => NoteDbStorage::class
                )
            ),
            NoteDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            TrackingService::class => array(
                'parameters' => array(
                    'repository' => TrackingDbStorage::class,
                    'accountStorage' => AccountApiStorage::class
                )
            ),
            TrackingRepository::class => array(
                'parameter' => array(
                    'storage' => TrackingCacheStorage::class,
                    'repository' => TrackingDbStorage::class
                )
            ),
            TrackingDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            AlertService::class => array(
                'parameters' => array(
                    'repository' => AlertDbStorage::class
                )
            ),
            AlertRepository::class => array(
                'parameter' => array(
                    'storage' => AlertCacheStorage::class,
                    'repository' => AlertDbStorage::class
                )
            ),
            AlertDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            ItemPersistentDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadMysqli',
                    'fastReadSql' => 'FastReadMysqli',
                    'writeSql' => 'WriteMysqli'
                ]
            ],
            FeeService::class => array(
                'parameters' => array(
                    'repository' => FeeDbStorage::class
                )
            ),
            FeeRepository::class => array(
                'parameter' => array(
                    'storage' => FeeCacheStorage::class,
                    'repository' => FeeDbStorage::class
                )
            ),
            FeeDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            GiftWrapService::class => array(
                'parameters' => array(
                    'repository' => GiftWrapDbStorage::class
                )
            ),
            GiftWrapRepository::class => array(
                'parameter' => array(
                    'storage' => GiftWrapCacheStorage::class,
                    'repository' => GiftWrapDbStorage::class
                )
            ),
            GiftWrapDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            UserChangeService::class => array(
                'parameters' => array(
                    'repository' => UserChangeDbStorage::class
                )
            ),
            UserChangeRepository::class => array(
                'parameter' => array(
                    'storage' => UserChangeCacheStorage::class,
                    'repository' => UserChangeDbStorage::class
                )
            ),
            UserChangeDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => UserChangeMapper::class,
                ]
            ],
            UserChangeMongoDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            BatchService::class => array(
                'parameters' => array(
                    'repository' => BatchDbStorage::class
                )
            ),
            BatchRepository::class => array(
                'parameter' => array(
                    'storage' => BatchCacheStorage::class,
                    'repository' => BatchDbStorage::class
                )
            ),
            BatchDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => BatchMapper::class
                )
            ),
            UserPreferenceService::class => array(
                'parameters' => array(
                    'repository' => UserPreferenceMongoDbStorage::class
                )
            ),
            UserPreferenceRepository::class => array(
                'parameter' => array(
                    'storage' => UserPreferenceCacheStorage::class,
                    'repository' => UserPreferenceMongoDbStorage::class
                )
            ),
            TagDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteCGSql',
                    'mapper' => TagMapper::class
                )
            ),
            TagRepository::class => array (
                'parameter' => array(
                    'storage' => TagCacheStorage::class,
                    'repository' => TagDbStorage::class
                )
            ),
            TagService::class => array(
                'parameter' => array (
                    'repository' => TagDbStorage::class
                )
            ),
            LabelMetaDataDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteCGSql',
                    'mapper' => LabelMapper::class
                ]
            ],
            LabelLabelDataS3Storage::class => [
                'parameter' => [
                    'predisClient' => 'unreliable_redis',
                ]
            ],
            LabelMetaPlusLabelDataStorage::class => [
                'parameter' => [
                    'metaDataStorage' => LabelMetaDataDbStorage::class,
                    'labelDataStorage' => LabelLabelDataS3Storage::class,
                ]
            ],
            LabelRepository::class => [
                'parameter' => [
                    'storage' => LabelCacheStorage::class,
                    'repository' => LabelMetaPlusLabelDataStorage::class
                ]
            ],
            AccountCommandService::class => array(
                'parameter' => array(
                    'accountStorage' => AccountApiStorage::class
                )
            ),
            AccountApiStorage::class => array(
                'parameter' => array(
                    'client' => 'account_guzzle'
                )
            ),
            AccountPollingWindowApiStorage::class => array(
                'parameter' => array(
                    'client' => 'account_guzzle'
                )
            ),
            FilterService::class => array(
                'parameter' => array(
                    'filterStorage' => FilterCache::class,
                    'filterEntityStorage' => FilterEntityCacheStorage::class
                )
            ),
            FilterCache::class => array(
                'parameter' => array(
                    'incrementor' => Incrementor::class
                )
            ),
            Incrementor::class => array(
                'parameter' => array(
                    'key' => "OrderFilters"
                )
            ),
            CancelDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            TemplateService::class => array(
                'parameters' => array(
                    'repository' => TemplateRepository::class
                )
            ),
            TemplateRepository::class => array(
                'parameter' => array(
                    'storage' => TemplateCacheStorage::class,
                    'repository' => TemplateMongoDbStorage::class
                )
            ),
            ShippingMethodService::class => [
                'parameter' => [
                    'repository' => ShippingMethodRepository::class
                ]
            ],
            ShippingMethodRepository::class => [
                'parameter' => [
                    'storage' => ShippingMethodCacheStorage::class,
                    'repository' => ShippingMethodDbStorage::class
                ]
            ],
            ShippingMethodDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadCGSql',
                    'fastReadSql' => 'FastReadCGSql',
                    'writeSql' => 'WriteCGSql',
                    'mapper' => ShippingMethodMapper::class
                ]
            ],
            OrganisationUnitService::class => [
                'parameter' => [
                    'repository' => OrganisationUnitApi::class
                ]
            ],
            OrganisationUnitApi::class => [
                'parameter' => [
                    'client' => 'directory_guzzle'
                ]
            ],
            InvoiceSettingsService::class => array(
                'parameters' => array(
                    'repository' => InvoiceSettingsRepository::class
                )
            ),
            InvoiceSettingsRepository::class => array(
                'parameter' => array(
                    'storage' => InvoiceSettingsCacheStorage::class,
                    'repository' => InvoiceSettingsMongoDbStorage::class
                )
            ),
            UsageDb::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageAggregateDb::class => [
                'parameter'=> [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageRepository::class => [
                'parameter' => [
                    'storage' => UsageRedis::class,
                    'repository' => UsageDb::class
                ]
            ],
            UsageRedis::class => [
                'parameter' => [
                    'client' => 'unreliable_redis',
                    'aggregateStorage' => UsageAggregateDb::class
                ]
            ],
            AliasSettingsService::class => array(
                'parameters' => array(
                    'repository' => AliasSettingsRepository::class,
                    'mapper' => AliasSettingsMapper::class
                )
            ),
            AliasSettingsMapper::class => array (
                'parameters' => array (
                    'shippingMethodMapper' => ShippingMethodMapper::class
                )
            ),
            AliasSettingsRepository::class => array(
                'parameter' => array (
                    'storage' => AliasSettingsCacheStorage::class,
                    'repository' => AliasSettingsDbStorage::class
                )
            ),
            AliasSettingsDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => AliasSettingsMapper::class
                )
            ),
            ImageService::class => [
                'parameter' => [
                    'repository' => ImageApi::class
                ]
            ],
            ImageApi::class => [
                'parameter' => [
                    'client' => 'image_guzzle'
                ]
            ],
            UpdateItemsTaxWorkload::class => [
                'parameters' => [
                    'organisationUnitId' => 0,
                    'sku' => '',
                ]
            ],
            ProductService::class => array(
                'parameters' => array(
                    'repository' => ProductRepository::class,
                    'mapper' => ProductMapper::class,
                    'stockStorage' => StockService::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class,
                    'updateItemsTaxWorkloadFactory' => UpdateItemsTaxWorkloadFactory::class,
                    'updateItemsImagesWorkloadFactory' => UpdateItemsImagesWorkloadFactory::class,
                )
            ),
            ProductClientService::class => [
                'parameters' => array(
                    'repository' => ProductRepository::class,
                    'mapper' => ProductMapper::class,
                    'stockStorage' => StockService::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class,
                    'updateItemsTaxWorkloadFactory' => UpdateItemsTaxWorkloadFactory::class,
                    'updateItemsImagesWorkloadFactory' => UpdateItemsImagesWorkloadFactory::class,
                )
            ],
            ProductRepository::class => array(
                'parameter' => array (
                    'storage' => ProductCacheStorage::class,
                    'repository' => ProductDbStorage::class
                )
            ),
            ProductDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => ProductMapper::class
                )
            ),
            ProductDetailRepository::class => [
                'parameter' => [
                    'storage' => ProductDetailCacheStorage::class,
                    'repository' => ProductDetailDbStorage::class
                ]
            ],
            ProductDetailDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => ProductDetailMapper::class
                ]
            ],
            TransactionRedisClient::class => [
                'parameter' => [
                    'predis' => 'reliable_redis', 
                ]
            ],
            StockService::class => [
                'parameter' => [
                    'repository' => StockRepository::class,
                    'locationStorage' => StockLocationService::class
                ]
            ],
            StockRepository::class => [
                'parameter' => [
                    'storage' => StockCacheStorage::class,
                    'repository' => StockDbStorage::class
                ]
            ],
            StockDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => StockMapper::class
                ]
            ],
            StockLocationService::class => [
                'parameter' => [
                    'repository' => StockLocationRepository::class,
                    'stockStorage' => StockRepository::class
                ]
            ],
            StockLocationRepository::class => [
                'parameter' => [
                    'storage' => StockLocationCacheStorage::class,
                    'repository' => StockLocationDbStorage::class
                ]
            ],
            StockLocationDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => StockLocationMapper::class
                ]
            ],
            StockAdjustmentCalculator::class => [
                'parameter' => [
                    'accountClient' => AccountApiStorage::class
                ]
            ],
            StockCreator::class => [
                'parameter' => [
                    'stockStorage' => StockRepository::class,
                ]
            ],
            StockLogRepository::class => [
                'parameter' => [
                    'storage' => StockLogCacheStorage::class,
                    'repository' => StockLogDbStorage::class
                ]
            ],
            StockLogDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => StockLogMapper::class
                ]
            ],
            ReSyncOrderCountsCommand::class => [
                'parameter' => [
                    'predisClient' => 'reliable_redis',
                ],
            ],
            OrderCountsRedisStorage::class => [
                'parameter' => [
                    'client' => 'reliable_redis',
                    'mapper' => OrderCountsMapper::class
                ]
            ],
            OrderCountsApiStorage::class => [
                'parameter' => [
                    'client' => "cg_app_guzzle",
                ]
            ],
            ListingService::class => [
                'parameters' => [
                    'mapper' => ListingMapper::class,
                    'stockStorage' => StockService::class
                ]
            ],
            ListingRepository::class => [
                'parameter' => [
                    'storage' => ListingCacheStorage::class,
                    'repository' => ListingDbStorage::class
                ]
            ],
            ListingDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => ListingMapper::class
                ]
            ],
            ListingClientService::class => [
                'parameters' => [
                    'mapper' => ListingMapper::class
                ]
            ],
            ListingDeprService::class => [
                'parameters' => [
                    'mapper' => ListingMapper::class
                ]
            ],
            UnimportedListingService::class => [
                'parameters' => [
                    'mapper' => UnimportedListingMapper::class,
                    'imageStorage' => ImageService::class
                ]
            ],
            UnimportedListingRepository::class => [
                'parameter' => [
                    'storage' => UnimportedListingCacheStorage::class,
                    'repository' => UnimportedListingDbStorage::class
                ]
            ],
            UnimportedListingDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadCGSql',
                    'fastReadSql' => 'FastReadCGSql',
                    'writeSql' => 'WriteCGSql',
                    'mapper' => UnimportedListingMapper::class
                ]
            ],
            LocationService::class => [
                'parameters' => [
                    'repository' => LocationRepository::class,
                    'mapper' => LocationMapper::class
                ]
            ],
            LocationRepository::class => [
                'parameter' => [
                    'storage' => LocationCacheStorage::class,
                    'repository' => LocationDbStorage::class
                ]
            ],
            LocationDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => LocationMapper::class
                ]
            ],
            OrderGeneratorCommand::class => [
                'parameter' => [
                    'factory' => SimpleOrderFactory::class,
                    'accountStorage' => AccountApiStorage::class,
                    'orderStorage' => OrderApiStorage::class,
                ]
            ],
            OrderApiStorage::class => [
                'parameter' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            'EkmOrderDownloadCommand' => [
                'parameter' => [
                    'factory' => EkmOrderUpdateGenerator::class
                ]
            ],
            PickListDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => PickListMapper::class
                ]
            ],
            PickListRepository::class => [
                'parameter' => [
                    'storage' => PickListCacheStorage::class,
                    'repository' => PickListDbStorage::class
                ]
            ],
            PickListService::class => [
                'parameters' => [
                    'repository' => PickListRepository::class,
                    'mapper' => PickListMapper::class
                ]
            ],
            UnimportedListingApi::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle'
                ]
            ],
            RedisChannel::class => [
                'parameters' => [
                    'rootOrganisationUnitProvider' => OrganisationUnitService::class
                ]
            ],
            RemoveThenCorrectImportedProducts::class => [
                'parameters' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            VariationAttributeMapService::class => [
                'parameters' => [
                    'repository' => VariationAttributeMapDbStorage::class,
                    'mapper' => VariationAttributeMapMapper::class
                ]
            ],
            VariationAttributeMapDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => VariationAttributeMapMapper::class
                ]
            ],
            UpdateAllItemsTaxCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            UpdateAllItemsImagesCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            CorrectStockOfItemsWithIncorrectStockManagedFlagCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql',
                    'predisClient' => 'reliable_redis',
                ]
            ],
            ApplyMissingStockAdjustmentsForCancDispRefOrdersCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            PhantomJSClient::class => [
                'instantiator' => 'JonnyW\PhantomJs\Client::getInstance',
                'injections' => [
                    'addOption' => [
                        ['option' => '--cookies-file=/tmp/cookie-jar/' . uniqid('', true)],
                        ['option' => '--local-storage-path=/var/www/cg_app/current/vendor/channelgrabber/php-phantomjs/bin/']
                    ],
                    'setPhantomJs' => [
                        ['path' => '/usr/bin/phantomjs']
                    ],
                    'setPhantomLoader' => [
                        ['path' => '/var/www/cg_app/current/vendor/channelgrabber/php-phantomjs/bin/phantomloader']
                    ]
                ]
            ],

            EkmTaxRateCache::class => [
                'parameter' => [
                    'mapper' => EkmTaxRateMapper::class
                ]
            ],
            EkmTaxRateDb::class => array(
                'parameters' => array(
                    'readSql' => 'ekmReadSql',
                    'fastReadSql' => 'ekmFastReadSql',
                    'writeSql' => 'ekmWriteSql',
                    'mapper' => EkmTaxRateMapper::class
                )
            ),
            EkmTaxRateRepository::class => [
                'parameters' => [
                    'storage' => EkmTaxRateCache::class,
                    'repository' => EkmTaxRateDb::class,
                ]
            ],
            EkmTaxRateService::class => [
                'parameters' => [
                    'cryptor' => 'ekm_cryptor',
                    'repository' => EkmTaxRateRepository::class,
                    'phantomJs' => PhantomJSClient::class
                ]
            ],
            SequentialNumberingProviderRedis::class => [
                'parameter' => [
                    'predisAsync' => 'reliable_redis_async',
                ]
            ],
            ZeroNegativeStockCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            ApiSettingsRepository::class => [
                'parameters' => [
                    'repository' => ApiSettingsDbStorage::class,
                    'storage' => ApiSettingsCacheStorage::class,
                ],
            ],
            ApiSettingsDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            NotificationQueue::class => [
                'parameters' => [
                    'predis' => 'reliable_redis',
                ],
            ],
            WooCommerceListingImport::class => [
                'parameters' => [
                    'enabled' => false,
                ],
            ],
            StockApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
            StockLocationApiStorage::class => [
                'parameters' => [
                    'client' => 'cg_app_guzzle',
                ],
            ],
            'StockApiService' => [
                'parameters' => [
                    'repository' => StockApiStorage::class,
                    'locationStorage' => StockLocationApiStorage::class,
                ],
            ],
            'StockLocationApiService' => [
                'parameters' => [
                    'repository' => StockLocationApiStorage::class,
                    'stockStorage' => StockApiStorage::class,
                ],
            ],
            StockAdjustmentCommand::class => [
                'parameters' => [
                    'stockService' => 'StockApiService',
                    'stockLocationService' => 'StockLocationApiService',
                ],
            ],
            UnimportedListingMarketplaceRepository::class => [
                'parameters' => [
                    'storage' => UnimportedListingMarketplaceCacheStorage::class,
                    'repository' => UnimportedListingMarketplaceDbStorage::class,
                ],
            ],
            UnimportedListingMarketplaceDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                ],
            ],
            ProductettingsRepository::class => [
                'parameters' => [
                    'storage' => ProductSettingsCacheStorage::class,
                    'repository' => ProductSettingsDbStorage::class,
                ],
            ],
            ProductSettingsDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            EnsureProductsAndListingsAssociatedWithRootOuCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            CorrectPendingListingsStatusFromSiblingListingsCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            AddSkusToListingsCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            DeleteAlreadyImportedUnimportedListingsCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            CreateMissingStockCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            RemoveDuplicateStockCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            ProductMapper::class => [
                'parameters' => [
                    'entityClass' => function() { return LockingProduct::class; },
                ],
            ],
            StockMapper::class => [
                'parameters' => [
                    'entityClass' => function() { return LockingStock::class; },
                ],
            ],
            StockLocationController::class => [
                'parameters' => [
                    'service' => StockLocationServiceService::class,
                ],
            ],
            StockLocationCollectionController::class => [
                'parameters' => [
                    'service' => StockLocationServiceService::class,
                ],
            ],
            CustomerCountRepository::class => [
                'parameters' => [
                    'storage' => CustomerCountCacheStorage::class,
                    'repository' => CustomerCountOrderLookupStorage::class,
                ],
            ],
            CustomerCountCacheStorage::class => [
                'parameters' => [
                    'client' => 'reliable_redis',
                ],
            ],
            AmazonShippingServiceRepository::class => [
                'parameters' => [
                    'storage' => AmazonShippingServiceCacheStorage::class,
                    'repository' => AmazonShippingServiceDbStorage::class,
                ],
            ],
            AmazonShippingServiceDbStorage::class => [
                'parameters' => [
                    'readSql' => 'amazonReadCGSql',
                    'fastReadSql' => 'amazonFastReadCGSql',
                    'writeSql' => 'amazonWriteCGSql',
                ],
            ],
            'amazonReadCGSql' => [
                'parameters' => [
                    'adapter' => 'amazonRead',
                ],
            ],
            'amazonFastReadCGSql' => [
                'parameters' => [
                    'adapter' => 'amazonFastRead',
                ],
            ],
            'amazonWriteCGSql' => [
                'parameters' => [
                    'adapter' => 'amazonWrite',
                ],
            ],
            AmazonShippingServiceService::class => [
                'parameters' => [
                    'cryptor' => 'amazon_cryptor',
                ],
            ],
            ListingStatusHistoryRepository::class => [
                'parameters' => [
                    'storage' => ListingStatusHistoryCacheStorage::class,
                    'repository' => ListingStatusHistoryDbStorage::class,
                ],
            ],
            ListingStatusHistoryDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                ],
            ],
            SalesAccountDisable::class => [
                'parameters' => [
                    'service' => AccountService::class,
                    'readSql' => 'ReadSql',
                ],
            ],
            SetupProgressSettingsRepository::class => [
                'parameters' => [
                    'storage' => SetupProgressSettingsCacheStorage::class,
                    'repository' => SetupProgressSettingsDbStorage::class,
                ],
            ],
            SetupProgressSettingsDbStorage::class => [
                'parameters' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => SetupProgressSettingsMapper::class,
                ],
            ],
            ExchangeRateService::class => [
                'parameters' => [
                    'repository' => 'ExchangeRateRepositoryPrimary',
                    'mapper' => ExchangeRateMapper::class
                ]
            ],
            'ExchangeRateRepositoryPrimary' => [
                'parameter' => [
                    'storage' => ExchangeRateCacheStorage::class,
                    'repository' => 'ExchangeRateRepositorySecondary'
                ]
            ],
            'ExchangeRateRepositorySecondary' => [
                'parameter' => [
                    'storage' => ExchangeRateDbStorage::class,
                    'repository' => ExchangeRateExternalApiStorage::class
                ]
            ],
            ExchangeRateDbStorage::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => ExchangeRateMapper::class
                ]
            ],
            AutoArchiveOrdersCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadCGSql'
                ]
            ],
            FindIncorrectlyAllocatedStockCommand::class => [
                'parameter' => [
                    'sqlClient' => 'ReadSql',
                ]
            ],
            InvoiceEmailerService::class => [
                'parameters' => [
                    'predisClient' => 'reliable_redis',
                ],
            ],
            'preferences' => [
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                'CG\Cache\ClientInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\IncrementInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\ClientPipelineInterface' => 'CG\Cache\Client\RedisPipeline',
                'CG\Cache\KeyGeneratorInterface' => 'CG\Cache\KeyGenerator\Redis',
                'CG\Cache\Strategy\SerialisationInterface' => 'CG\Cache\Strategy\Serialisation\Serialize',
                'CG\Cache\Strategy\CollectionInterface' => 'CG\Cache\Strategy\Collection\Entities',
                'CG\Cache\Strategy\EntityInterface' => 'CG\Cache\Strategy\Entity\Standard',
                StorageInterface::class => Predis::class,
                \MongoClient::class => 'mongodb',
                EventManagerInterface::class => CGEventManager::class,
                IncrementorInterface::class => Incrementor::class,
                UsageStorageInterface::class => UsageRepository::class,
                LockClientInterface::class => TransactionRedisClient::class,
                TransactionClientInterface::class => TransactionRedisClient::class,
                /* Cache disabled for order items as not compatable with collection hydration strategy */
                ItemStorageInterface::class => ItemPersistentDbStorage::class,
                StockStorage::class => StockService::class,
                SequentialNumberingProviderInterface::class => SequentialNumberingProviderRedis::class,
                // We are NOT currently using the Cache storage for Orders, go straight to persistance
                OrderStorage::class => OrderPersistentStorage::class,
                ProductDetailStorage::class => ProductDetailRepository::class,
                ApiSettingsStorage::class => ApiSettingsRepository::class,
                UnimportedListingMarketplaceStorage::class => UnimportedListingMarketplaceRepository::class,
                OrderCountsStorage::class => OrderCountsRedisStorage::class,
                ProductSettingsStorage::class => ProductettingsRepository::class,
                ProductStorage::class => ProductRepository::class,
                ListingStorage::class => ListingDbStorage::class,
                UnimportedListingStorage::class => UnimportedListingDbStorage::class,
                AccountPollingWindowStorage::class => AccountPollingWindowApiStorage::class,
                ChannelShippingChannelsProviderInterface::class => DataplugCarriers::class,
                StockLocationStorage::class => StockLocationRepository::class,
                LocationStorage::class => LocationRepository::class,
                OrderClientStorage::class => OrderApiStorage::class,
                // Not using Cache storage for now as no easy way to invalidate it when either table changes
                StockLogStorage::class => StockLogDbStorage::class,
                LockingStorage::class => LockingRedisStorage::class,
                FilterEntityStorage::class => FilterEntityCacheStorage::class,
                CustomerCountStorage::class => CustomerCountRepository::class,
                AmazonShippingServiceStorage::class => AmazonShippingServiceRepository::class,
                LabelStorage::class => LabelRepository::class,
                ListingStatusHistoryStorage::class => ListingStatusHistoryRepository::class,
                SetupProgressSettingsStorage::class => SetupProgressSettingsRepository::class,
                OrderService::class => OrderLockingService::class,
                ItemService::class => ItemLockingService::class,
                ItemInvalidationService::class => ItemLockingService::class,
            ]
        )
    )
);

$configFiles = glob(__DIR__ . '/global/*.php');
foreach ($configFiles as $configFile) {
    $configFileContents = require_once $configFile;
    $config = \Zend\Stdlib\ArrayUtils::merge($config, $configFileContents);
}
return $config;