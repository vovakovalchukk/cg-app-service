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

use Zend\Db\Sql\Sql;
use CG\Zend\Stdlib\Db\Sql\Sql as CGSql;
use CG\Cache\Client\Redis as CacheRedis;
use CG\Cache\Client\RedisPipeline as CacheRedisPipeline;
use CG\ETag\Storage\Predis as EtagRedis;
use Zend\Di\Di;
use Zend\Config\Config;
use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager as CGEventManager;
use CG\Cache\IncrementorInterface;
use CG\Cache\Increment\Incrementor;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitApi;

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
use CG\Account\Client\Storage\Api as AccountApiStorage;
use CG\Account\Client\PollingWindow\Storage\Api as PollingWindowApiStorage;

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
use CG\Settings\Alias\Service as AliasSettingsService;
use CG\Settings\Alias\Mapper as AliasSettingsMapper;
use CG\Settings\Alias\Storage\Cache as AliasSettingsCacheStorage;
use CG\Settings\Alias\Storage\Db as AliasSettingsDbStorage;
use CG\Settings\Alias\Repository as AliasSettingsRepository;

//Usage
use CG\Usage\Storage\Db as UsageDb;
use CG\Usage\Aggregate\Storage\Db as UsageAggregateDb;
use CG\Usage\Storage\Redis as UsageRedis;
use CG\Usage\Repository as UsageRepository;
use CG\Usage\StorageInterface as UsageStorageInterface;

// Product
use CG\Product\Service as ProductService;
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
use CG\Stock\Location\Service as LocationService;
use CG\Stock\Location\Repository as LocationRepository;
use CG\Stock\Location\Storage\Cache as LocationCacheStorage;
use CG\Stock\Location\Storage\Db as LocationDbStorage;
use CG\Stock\Location\Mapper as LocationMapper;

return array(
    'service_manager' => array(
        'factories' => array(
            Di::Class => function($serviceManager) {
                $configuration = $serviceManager->get('config');

                $definition = new CG\Di\Definition\RuntimeDefinition(null, require dirname(dirname(__DIR__)) .  '/vendor/composer/autoload_classmap.php');
                $definitionList = new Zend\Di\DefinitionList([$definition]);
                $im = new Zend\Di\InstanceManager();
                $di = new Zend\Di\Di($definitionList, $im, new Zend\Di\Config(
                    isset($configuration['di']) ? $configuration['di'] : array()
                ));

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, 'Zend\Db\Adapter\Adapter');
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, 'Di');
                $im->addSharedInstance($di, 'Zend\Di\Di');
                $im->addSharedInstance($di->get('config', array('array' => $configuration)), 'config');
                $im->addSharedInstance($di->get(Config::class, array('array' => $configuration)), 'app_config');

                return $di;
            }
        ),
        'shared' => array(
            'Zend\Di\Di' => true
        ),
        'aliases' => array(
            'Di' => 'Zend\Di\Di'
        )
    ),
    'di' => array(
        'instance' => array(
            'aliases' => array(
                'ReadCGSql' => CGSql::class,
                'FastReadCGSql' => CGSql::class,
                'WriteCGSql' => CGSql::class,
                'Di' => Di::class,
                'config' => Config::class,
                'app_config' => Config::class
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
            CacheRedisPipeline::class => array(
                'parameter' => array(
                    'predis' => 'unreliable_redis'
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
                    'filterEntityStorage' => FilterEntityCacheStorage::class
                )
            ),
            OrderPersistentDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
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
            OrderDownloadCommand::class => array(
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
            ProductService::class => array(
                'parameters' => array(
                    'repository' => ProductRepository::class,
                    'mapper' => ProductMapper::class
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
                    'repository' => StockRepository::class,
                    'locationStorage' => LocationService::class
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
            LocationService::class => [
                'parameter' => [
                    'repository' => LocationRepository::class
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
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                'CG\Cache\ClientInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\IncrementInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\ClientPipelineInterface' => 'CG\Cache\Client\RedisPipeline',
                'CG\Cache\KeyGeneratorInterface' => 'CG\Cache\KeyGenerator\Redis',
                'CG\Cache\Strategy\SerialisationInterface' => 'CG\Cache\Strategy\Serialisation\Serialize',
                'CG\Cache\Strategy\CollectionInterface' => 'CG\Cache\Strategy\Collection\Entities',
                'CG\Cache\Strategy\EntityInterface' => 'CG\Cache\Strategy\Entity\Standard',
                'CG\ETag\StorageInterface' => 'CG\ETag\Storage\Predis',
                \MongoClient::class => 'mongodb',
                EventManagerInterface::class => CGEventManager::class,
                IncrementorInterface::class => Incrementor::class,
                'Di' => 'Zend\Di\Di',
                'config' => Config::class,
                UsageStorageInterface::class => UsageRepository::class,
                LockClientInterface::class => TransactionRedisClient::class,
                TransactionClientInterface::class => TransactionRedisClient::class
             )
        )
    )
);
