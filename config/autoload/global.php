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

//Polling Window
use CG\Account\Service\PollingWindow\Service as AccountPollingWindowService;
use CG\Account\Shared\PollingWindow\Repository as AccountPollingWindowRepository;
use CG\Account\Service\PollingWindow\Storage\Cache as AccountPollingWindowCacheStorage;
use CG\Account\Service\PollingWindow\Storage\Db as AccountPollingWindowDbStorage;
use CG\Account\Shared\PollingWindow\Mapper as AccountPollingWindowMapper;

//Order
use CG\Order\Service\Service as OrderService;
use CG\Order\Shared\Repository as OrderRepository;
use CG\Order\Service\Storage\Cache as OrderCacheStorage;
use CG\Order\Service\Storage\Persistent as OrderPersistentStorage;
use CG\Order\Service\Storage\Persistent\Db as OrderPersistentDbStorage;
use CG\Order\Service\Storage\ElasticSearch as OrderElasticSearchStorage;

//Note
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Repository as NoteRepository;
use CG\Order\Service\Note\Storage\Cache as NoteCacheStorage;
use CG\Order\Service\Note\Storage\Db as NoteDbStorage;

//Tracking
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Shared\Tracking\Repository as TrackingRepository;
use CG\Order\Service\Tracking\Storage\Cache as TrackingCacheStorage;
use CG\Order\Service\Tracking\Storage\Db as TrackingDbStorage;

//Alert
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Shared\Alert\Repository as AlertRepository;
use CG\Order\Service\Alert\Storage\Cache as AlertCacheStorage;
use CG\Order\Service\Alert\Storage\Db as AlertDbStorage;

//Item
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Shared\Item\Repository as ItemRepository;
use CG\Order\Service\Item\Storage\Cache as ItemCacheStorage;
use CG\Order\Service\Item\Storage\Persistent as ItemPersistentStorage;
use CG\Order\Service\Item\Storage\Persistent\Db as ItemPersistentDbStorage;
use CG\Order\Service\Item\Transaction\UpdateItemAndStock as UpdateItemAndStockTransaction;

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
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Shared\UserChange\Repository as UserChangeRepository;
use CG\Order\Service\UserChange\Storage\Cache as UserChangeCacheStorage;
use CG\Order\Service\UserChange\Storage\MongoDb as UserChangeMongoDbStorage;

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

//Cilex Command
use CG\Channel\Command\Order\Download as OrderDownloadCommand;
use CG\Channel\Command\Order\Generator as OrderGeneratorCommand;
use CG\Channel\Command\Order\Generator\SimpleOrderFactory;
use CG\Account\Client\PollingWindow\Storage\Api as PollingWindowApiStorage;
use CG\Channel\Command\Service as AccountCommandService;
use CG\Ekm\Gearman\Generator\OrderDownload as EkmOrderUpdateGenerator;

//Filter
use CG\Order\Service\Filter\Service as FilterService;
use CG\Order\Service\Filter\Storage\Cache as FilterCache;
use CG\Order\Service\Filter\Entity\Storage\Cache as FilterEntityCacheStorage;

//Template
use CG\Template\Service as TemplateService;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Storage\Cache as TemplateCacheStorage;
use CG\Template\Storage\MongoDb as TemplateMongoDbStorage;

//Cancel
use CG\Order\Service\Cancel\Storage\Db as CancelDbStorage;

//Shipping
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
use CG\Product\Service\Service as ProductService;
use CG\Product\Repository as ProductRepository;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Storage\Db as ProductDbStorage;
use CG\Product\Storage\Cache as ProductCacheStorage;

// Transaction
use CG\Transaction\ClientInterface as TransactionClientInterface;
use CG\Transaction\LockInterface as LockClientInterface;
use CG\Transaction\Client\Redis as TransactionRedisClient;

// Stock
use CG\Stock\AdjustmentCalculator as StockAdjustmentCalculator;
use CG\Stock\Service as StockService;
use CG\Stock\Repository as StockRepository;
use CG\Stock\Storage\Cache as StockCacheStorage;
use CG\Stock\Storage\Db as StockDbStorage;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Location\Service as StockLocationService;
use CG\Stock\Location\Repository as StockLocationRepository;
use CG\Stock\Location\Storage\Cache as StockLocationCacheStorage;
use CG\Stock\Location\Storage\Db as StockLocationDbStorage;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Audit\Storage\Queue as StockAuditQueue;

// Listing
use CG\Listing\Service as ListingService;
use CG\Listing\Repository as ListingRepository;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Storage\Db as ListingDbStorage;
use CG\Listing\Storage\Cache as ListingCacheStorage;

// Unimported Listing
use CG\Listing\Unimported\Service as UnimportedListingService;
use CG\Listing\Unimported\Repository as UnimportedListingRepository;
use CG\Listing\Unimported\Mapper as UnimportedListingMapper;
use CG\Listing\Unimported\Storage\Db as UnimportedListingDbStorage;
use CG\Listing\Unimported\Storage\Cache as UnimportedListingCacheStorage;

use CG\Image\Service as ImageService;
use CG\Image\Storage\Api as ImageApi;

// Location
use CG\Location\Service as LocationService;
use CG\Location\Repository as LocationRepository;
use CG\Location\Mapper as LocationMapper;
use CG\Location\Storage\Db as LocationDbStorage;
use CG\Location\Storage\Cache as LocationCacheStorage;

return array(
    'di' => array(
        'instance' => array(
            'aliases' => array(
                'ReadCGSql' => CGSql::class,
                'FastReadCGSql' => CGSql::class,
                'WriteCGSql' => CGSql::class,
                'EkmOrderDownloadCommand' => OrderDownloadCommand::class,
                'LiveOrderPersistentDbStorage' => OrderPersistentDbStorage::class,
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
            AccountService::class => array(
                'parameters' => array(
                    'repository' => AccountApiStorage::class,
                )
            ),
            AccountPollingWindowService::class => array(
                'parameter' => array(
                    'repository' => AccountPollingWindowRepository::class
                )
            ),
            AccountPollingWindowRepository::class => array(
                'parameter' => array(
                    'storage' => AccountPollingWindowCacheStorage::class,
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
                    'repository' => OrderRepository::class,
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
            OrderPersistentStorage::class => [
                'parameter' => [
                    'sqlClient' => OrderPersistentDbStorage::class,
                    'liveSqlClient' => 'LiveOrderPersistentDbStorage',
                ]
            ],
            NoteService::class => array(
                'parameters' => array(
                    'repository' => NoteRepository::class
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
                    'repository' => TrackingRepository::class,
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
                    'repository' => AlertRepository::class
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
            ItemService::class => array(
                'parameters' => array(
                    'repository' => ItemRepository::class
                )
            ),
            ItemRepository::class => array(
                'parameter' => array(
                    'storage' => ItemCacheStorage::class,
                    'repository' => ItemPersistentStorage::class
                )
            ),
            ItemPersistentDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            FeeService::class => array(
                'parameters' => array(
                    'repository' => FeeRepository::class
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
                    'repository' => GiftWrapRepository::class
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
                    'repository' => UserChangeRepository::class
                )
            ),
            UserChangeRepository::class => array(
                'parameter' => array(
                    'storage' => UserChangeCacheStorage::class,
                    'repository' => UserChangeMongoDbStorage::class
                )
            ),
            UserChangeMongoDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            BatchService::class => array(
                'parameters' => array(
                    'repository' => BatchRepository::class
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
                    'repository' => UserPreferenceRepository::class
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
                    'writeSql' => 'WriteSql',
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
                    'repository' => TagRepository::class
                )
            ),
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
            PollingWindowApiStorage::class => array(
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
            ProductService::class => array(
                'parameters' => array(
//                    'repository' => ProductRepository::class,
                    'repository' => ProductDbStorage::class, // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
                    'mapper' => ProductMapper::class,
                    'stockStorage' => StockService::class,
                    'listingStorage' => ListingService::class,
                    'imageStorage' => ImageService::class
                )
            ),
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
            TransactionRedisClient::class => [
                'parameter' => [
                    'predis' => 'unreliable_redis',
                ]
            ],
            StockService::class => [
                'parameter' => [
//                    'repository' => StockRepository::class,
                    'repository' => StockDbStorage::class, // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
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
//                    'repository' => StockLocationRepository::class
                    'repository' => StockLocationDbStorage::class // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
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
                    'accountClient' => AccountApiStorage::class,
                    'itemClient' => ItemRepository::class
                ]
            ],
            UpdateItemAndStockTransaction::class => [
                'parameter' => [
                    'itemStorage' => ItemRepository::class
                ]
            ],
            ListingService::class => [
                'parameters' => [
//                    'repository' => ListingRepository::class,
                    'repository' => ListingDbStorage::class, // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
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
            UnimportedListingService::class => [
                'parameters' => [
//                    'repository' => UnimportedListingRepository::class,
                    'repository' => UnimportedListingDbStorage::class, // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
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
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => UnimportedListingMapper::class
                ]
            ],
            LocationService::class => [
                'parameters' => [
//                    'repository' => LocationRepository::class,
                    'repository' => LocationDbStorage::class, // TODO: QUICK FIX UNTIL CGIV-4085 IS OUT
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
                    'orderStorage' => OrderRepository::class,
                    'orderItemStorage' => ItemRepository::class,
                ],
            ],
            'EkmOrderDownloadCommand' => [
                'parameter' => [
                    'factory' => EkmOrderUpdateGenerator::class
                ]
            ],
            StockAuditQueue::class => [
                'parameters' => [
                    'client' => 'reliable_redis'
                ]
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
                TransactionClientInterface::class => TransactionRedisClient::class
            ]
        )
    )
);
