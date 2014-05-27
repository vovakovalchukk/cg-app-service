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
use CG\Cache\Client\Redis as CacheRedis;
use CG\Cache\Client\RedisPipeline as CacheRedisPipeline;
use CG\ETag\Storage\Predis as EtagRedis;
use Zend\Di\Di;
use Zend\EventManager\EventManager;
use Zend\Config\Config;
use CG\Cache\EventManagerInterface;
use CG\Zend\Stdlib\Cache\EventManager as CGEventManager;
use CG\Cache\IncrementorInterface;
use CG\Cache\Increment\Incrementor;

//Service
use CG\App\Service\Service as ServiceService;
use CG\App\Service\Repository as ServiceRepository;
use CG\App\Service\Storage\Cache as ServiceCacheStorage;
use CG\App\Service\Storage\Db as ServiceDbStorage;
use CG\Controllers\App\Service as ServiceController;
use CG\Controllers\App\Service\Collection as ServiceCollectionController;
use CG\App\Service\Storage\ETag as ServiceETagStorage;

//Event
use CG\App\Service\Event\Service as EventService;
use CG\App\Service\Event\Repository as EventRepository;
use CG\App\Service\Event\Storage\Cache as EventCacheStorage;
use CG\App\Service\Event\Storage\Db as EventDbStorage;
use CG\Controllers\App\Event as EventController;
use CG\Controllers\App\Event\Collection as EventCollectionController;
use CG\App\Service\Event\Storage\ETag as EventETagStorage;

//Order
use CG\Order\Service\Service as OrderService;
use CG\Order\Shared\Repository as OrderRepository;
use CG\Order\Service\Storage\Cache as OrderCacheStorage;
use CG\Order\Service\Storage\Persistent as OrderPersistentStorage;
use CG\Order\Service\Storage\Persistent\Db as OrderPersistentDbStorage;
use CG\Controllers\Order\Order as OrderController;
use CG\Controllers\Order\Order\Collection as OrderCollectionController;
use CG\Order\Service\Storage\ETag as OrderETagStorage;
use CG\Order\Service\Storage\ElasticSearch as OrderElasticSearchStorage;

//Note
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Repository as NoteRepository;
use CG\Order\Service\Note\Storage\Cache as NoteCacheStorage;
use CG\Order\Service\Note\Storage\Db as NoteDbStorage;
use CG\Controllers\Order\Note as NoteController;
use CG\Controllers\Order\Note\Collection as NoteCollectionController;
use CG\Order\Service\Note\Storage\ETag as NoteETagStorage;

//Tracking
use CG\Order\Service\Tracking\Service as TrackingService;
use CG\Order\Shared\Tracking\Repository as TrackingRepository;
use CG\Order\Service\Tracking\Storage\Cache as TrackingCacheStorage;
use CG\Order\Service\Tracking\Storage\Db as TrackingDbStorage;
use CG\Controllers\Order\Tracking as TrackingController;
use CG\Controllers\Order\Tracking\Collection as TrackingCollectionController;
use CG\Order\Service\Tracking\Storage\ETag as TrackingETagStorage;

//Alert
use CG\Order\Service\Alert\Service as AlertService;
use CG\Order\Shared\Alert\Repository as AlertRepository;
use CG\Order\Service\Alert\Storage\Cache as AlertCacheStorage;
use CG\Order\Service\Alert\Storage\Db as AlertDbStorage;
use CG\Controllers\Order\Alert as AlertController;
use CG\Controllers\Order\Alert\Collection as AlertCollectionController;
use CG\Order\Service\Alert\Storage\ETag as AlertETagStorage;

//Archive
use CG\Controllers\Order\Archive as ArchiveController;

//Item
use CG\Order\Service\Item\Service as ItemService;
use CG\Order\Shared\Item\Repository as ItemRepository;
use CG\Order\Service\Item\Storage\Cache as ItemCacheStorage;
use CG\Order\Service\Item\Storage\Persistent as ItemPersistentStorage;
use CG\Order\Service\Item\Storage\Persistent\Db as ItemPersistentDbStorage;
use CG\Controllers\Order\Item as ItemController;
use CG\Controllers\Order\Item\Collection as ItemCollectionController;
use CG\Order\Service\Item\Storage\ETag as ItemETagStorage;

//Fee
use CG\Order\Service\Item\Fee\Service as FeeService;
use CG\Order\Shared\Item\Fee\Repository as FeeRepository;
use CG\Order\Service\Item\Fee\Storage\Cache as FeeCacheStorage;
use CG\Order\Service\Item\Fee\Storage\Db as FeeDbStorage;
use CG\Controllers\Order\Item\Fee as FeeController;
use CG\Controllers\Order\Item\Fee\Collection as FeeCollectionController;
use CG\Order\Service\Item\Fee\Storage\ETag as FeeETagStorage;

//GiftWrap
use CG\Order\Service\Item\GiftWrap\Service as GiftWrapService;
use CG\Order\Shared\Item\GiftWrap\Repository as GiftWrapRepository;
use CG\Order\Service\Item\GiftWrap\Storage\Cache as GiftWrapCacheStorage;
use CG\Order\Service\Item\GiftWrap\Storage\Db as GiftWrapDbStorage;
use CG\Controllers\Order\Item\GiftWrap as GiftWrapController;
use CG\Controllers\Order\Item\GiftWrap\Collection as GiftWrapCollectionController;
use CG\Order\Service\Item\GiftWrap\Storage\ETag as GiftWrapETagStorage;

//UserChange
use CG\Order\Service\UserChange\Service as UserChangeService;
use CG\Order\Shared\UserChange\Repository as UserChangeRepository;
use CG\Order\Service\UserChange\Storage\Cache as UserChangeCacheStorage;
use CG\Order\Service\UserChange\Storage\MongoDb as UserChangeMongoDbStorage;
use CG\Controllers\Order\UserChange as UserChangeController;
use CG\Order\Service\UserChange\Storage\ETag as UserChangeETagStorage;

//Batch
use CG\Order\Service\Batch\Service as BatchService;
use CG\Order\Shared\Batch\Repository as BatchRepository;
use CG\Order\Service\Batch\Storage\Cache as BatchCacheStorage;
use CG\Order\Service\Batch\Storage\Db as BatchDbStorage;
use CG\Order\Shared\Batch\Mapper as BatchMapper;
use CG\Controllers\Order\Batch as BatchController;
use CG\Controllers\Order\Batch\Collection as BatchCollectionController;
use CG\Order\Service\Batch\Storage\ETag as BatchETagStorage;

//UserPreference
use CG\UserPreference\Service\Service as UserPreferenceService;
use CG\UserPreference\Shared\Repository as UserPreferenceRepository;
use CG\UserPreference\Service\Storage\Cache as UserPreferenceCacheStorage;
use CG\UserPreference\Service\Storage\MongoDb as UserPreferenceMongoDbStorage;
use CG\Controllers\UserPreference\UserPreference as UserPreferenceController;
use CG\Controllers\UserPreference\UserPreference\Collection as UserPreferenceCollectionController;
use CG\UserPreference\Service\Storage\ETag as UserPreferenceETagStorage;

//Tag
use CG\Order\Service\Tag\Service as TagService;
use CG\Order\Shared\Tag\Repository as TagRepository;
use CG\Order\Service\Tag\Storage\Cache as TagCacheStorage;
use CG\Order\Service\Tag\Storage\Db as TagDbStorage;
use CG\Order\Shared\Tag\Mapper as TagMapper;

//Cilex Command
use CG\Channel\Command\OrderDownload as OrderDownloadCommand;
use CG\Account\Client\Storage\Api as AccountApiStorage;

//Filter
use CG\Order\Service\Filter\Service as FilterService;
use CG\Order\Service\Filter\Storage\Cache as FilterCache;
use CG\Order\Service\Filter\Entity\Storage\Cache as FilterEntityCacheStorage;

//Template
use CG\Template\Service as TemplateService;
use CG\Template\Repository as TemplateRepository;
use CG\Template\Storage\Cache as TemplateCacheStorage;
use CG\Template\Storage\MongoDb as TemplateMongoDbStorage;
use CG\Template\Mapper as TemplateMapper;
use CG\Controllers\Template\Template as TemplateController;
use CG\Controllers\Template\Template\Collection as TemplateCollectionController;
use CG\Template\Storage\ETag as TemplateETagStorage;

//Cancel
use CG\Order\Service\Cancel\Storage\Db as CancelDbStorage;

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
                'ReadSql' => Sql::class,
                'FastReadSql' => Sql::class,
                'WriteSql' => Sql::class,
                'Di' => Di::class,
                'config' => Config::class,
                'ServiceService' => ServiceService::class,
                'ServiceCollectionService' => ServiceService::class,
                'EventService' => EventService::class,
                'EventCollectionService' => EventService::class,
                'ServiceDbRepo' => ServiceRepository::class,
                'ServiceDbStorage' => ServiceDbStorage::class,
                'ServiceCacheRepo' => ServiceRepository::class,
                'EventDbRepo' => EventRepository::class,
                'EventDbStorage' => EventDbStorage::class,
                'EventCacheRepo' => EventRepository::class,
                'OrderService' => OrderService::class,
                'OrderCollectionService' => OrderService::class,
                'NoteService' => NoteService::class,
                'NoteCollectionService' => NoteService::class,
                'TrackingService' => TrackingService::class,
                'TrackingCollectionService' => TrackingService::class,
                'AlertService' => AlertService::class,
                'AlertCollectionService' => AlertService::class,
                'ItemService' => ItemService::class,
                'ItemCollectionService' => ItemService::class,
                'FeeService' => FeeService::class,
                'FeeCollectionService' => FeeService::class,
                'GiftWrapService' => GiftWrapService::class,
                'GiftWrapCollectionService' => GiftWrapService::class,
                'UserChangeService' => UserChangeService::class,
                'UserChangeCollectionService' => UserChangeService::class,
                'BatchService' => BatchService::class,
                'BatchCollectionService' => BatchService::class,
                'UserPreferenceService' => UserPreferenceService::class,
                'UserPreferenceCollectionService' => UserPreferenceService::class,
                'TemplateService' => TemplateService::class,
                'TemplateCollectionService' => TemplateService::class,
            ),
            'ReadSql' => array(
                'parameter' => array(
                    'adapter' => 'readAdapter'
                )
            ),
            'FastReadSql' => array(
                'parameter' => array(
                    'adapter' => 'fastReadAdapter'
                )
            ),
            'WriteSql' => array(
                'parameter' => array(
                    'adapter' => 'writeAdapter'
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
            ServiceETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => ServiceRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_App_Service_Shared_Entity'
                )
            ),
            ServiceController::class => array(
                'parameters' => array(
                    'service' => 'ServiceService'
                )
            ),
            ServiceCollectionController::class => array(
                'parameters' => array(
                    'service' => 'ServiceCollectionService'
                )
            ),
            'ServiceService' => array(
                'parameters' => array(
                    'repository' => ServiceETagStorage::class,
                    'eventService' => 'EventCollectionService'
                )
            ),
            'ServiceCollectionService' => array(
                'parameters' => array(
                    'repository' => ServiceRepository::class,
                    'eventService' => 'EventCollectionService'
                )
            ),
            ServiceRepository::class => array(
                'parameter' => array(
                    'storage' => ServiceCacheStorage::class,
                    'repository' => ServiceDbStorage::class
                )
            ),
            ServiceDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            EventETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => EventRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_App_Event_Shared_Entity'
                )
            ),
            EventController::class => array(
                'parameters' => array(
                    'service' => 'EventService'
                )
            ),
            EventCollectionController::class => array(
                'parameters' => array(
                    'service' => 'EventCollectionService'
                )
            ),
            'EventService' => array(
                'parameters' => array(
                    'repository' => EventETagStorage::class
                )
            ),
            'EventCollectionService' => array(
                'parameters' => array(
                    'repository' => EventRepository::class
                )
            ),
            EventRepository::class => array(
                'parameter' => array(
                    'storage' => EventCacheStorage::class,
                    'repository' => EventDbStorage::class
                )
            ),
            EventDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'eventManager' => EventManager::class
                )
            ),
            'EventCacheRepo' => array(
                'parameter' => array(
                    'storage' => EventCache::class,
                    'repository' => 'EventDbRepo'
                )
            ),
            OrderPersistentStorage::class => array(
                'parameter' => array(
                    'tagService' => TagService::class
                )
            ),
            OrderRepository::class => array(
                'parameter' => array(
                    'storage' => OrderCacheStorage::class,
                    'repository' => OrderPersistentStorage::class
                )
            ),
            'OrderService' => array(
                'parameters' => array(
                    'repository' => OrderETagStorage::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'noteService' => 'NoteCollectionService',
                    'itemService' => 'ItemCollectionService',
                    'alertService' => 'AlertCollectionService',
                    'trackingService' => 'TrackingCollectionService',
                    'userChangeService' => 'UserChangeCollectionService',
                    'filterEntityStorage' => FilterEntityCacheStorage::class
                )
            ),
            'OrderCollectionService' => array(
                'parameters' => array(
                    'repository' => OrderRepository::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'noteService' => 'NoteCollectionService',
                    'itemService' => 'ItemCollectionService',
                    'alertService' => 'AlertCollectionService',
                    'trackingService' => 'TrackingCollectionService',
                    'userChangeService' => 'UserChangeCollectionService',
                    'filterEntityStorage' => FilterEntityCacheStorage::class
                )
            ),
            OrderETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => OrderRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Shared_Entity'
                )
            ),
            OrderController::class => array(
                'parameters' => array(
                    'service' => 'OrderService'
                )
            ),
            OrderCollectionController::class => array(
                'parameters' => array(
                    'service' => 'OrderCollectionService'
                )
            ),
            OrderPersistentDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            NoteETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => NoteRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Note_Shared_Entity'
                )
            ),
            NoteController::class => array(
                'parameters' => array(
                    'service' => 'NoteService'
                )
            ),
            NoteCollectionController::class => array(
                'parameters' => array(
                    'service' => 'NoteCollectionService'
                )
            ),
            'NoteService' => array(
                'parameters' => array(
                    'repository' => NoteETagStorage::class
                )
            ),
            'NoteCollectionService' => array(
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
            TrackingETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => TrackingRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Tracking_Shared_Entity'
                )
            ),
            TrackingController::class => array(
                'parameters' => array(
                    'service' => 'TrackingService'
                )
            ),
            TrackingCollectionController::class => array(
                'parameters' => array(
                    'service' => 'TrackingCollectionService'
                )
            ),
            'TrackingService' => array(
                'parameters' => array(
                    'repository' => TrackingETagStorage::class
                )
            ),
            'TrackingCollectionService' => array(
                'parameters' => array(
                    'repository' => TrackingRepository::class
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
            AlertETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => AlertRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Alert_Shared_Entity'
                )
            ),
            AlertController::class => array(
                'parameters' => array(
                    'service' => 'AlertService'
                )
            ),
            AlertCollectionController::class => array(
                'parameters' => array(
                    'service' => 'AlertCollectionService'
                )
            ),
            'AlertService' => array(
                'parameters' => array(
                    'repository' => AlertETagStorage::class
                )
            ),
            'AlertCollectionService' => array(
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
            ArchiveController::class => array(
                'parameters' => array(
                    'service' => 'OrderCollectionService'
                )
            ),
            ItemETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => ItemRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Item_Shared_Entity'
                )
            ),
            ItemController::class => array(
                'parameters' => array(
                    'service' => 'ItemService'
                )
            ),
            ItemCollectionController::class => array(
                'parameters' => array(
                    'service' => 'ItemCollectionService'
                )
            ),
            'ItemService' => array(
                'parameters' => array(
                    'repository' => ItemETagStorage::class,
                    'feeService' => 'FeeCollectionService',
                    'giftWrapService' => 'GiftWrapCollectionService'
                )
            ),
            'ItemCollectionService' => array(
                'parameters' => array(
                    'repository' => ItemRepository::class,
                    'feeService' => 'FeeCollectionService',
                    'giftWrapService' => 'GiftWrapCollectionService'
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
            FeeETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => FeeRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Fee_Shared_Entity'
                )
            ),
            FeeController::class => array(
                'parameters' => array(
                    'service' => 'FeeService'
                )
            ),
            FeeCollectionController::class => array(
                'parameters' => array(
                    'service' => 'FeeCollectionService'
                )
            ),
            'FeeService' => array(
                'parameters' => array(
                    'repository' => FeeETagStorage::class
                )
            ),
            'FeeCollectionService' => array(
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
            GiftWrapETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => GiftWrapRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_GiftWrap_Shared_Entity'
                )
            ),
            GiftWrapController::class => array(
                'parameters' => array(
                    'service' => 'GiftWrapService'
                )
            ),
            GiftWrapCollectionController::class => array(
                'parameters' => array(
                    'service' => 'GiftWrapCollectionService'
                )
            ),
            'GiftWrapService' => array(
                'parameters' => array(
                    'repository' => GiftWrapETagStorage::class
                )
            ),
            'GiftWrapCollectionService' => array(
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
            UserChangeETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => UserChangeRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_UserChange_Shared_Entity'
                )
            ),
            UserChangeController::class => array(
                'parameters' => array(
                    'service' => 'UserChangeService'
                )
            ),
            'UserChangeService' => array(
                'parameters' => array(
                    'repository' => UserChangeETagStorage::class
                )
            ),
            'UserChangeCollectionService' => array(
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
            BatchETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => BatchRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Batch_Shared_Entity'
                )
            ),
            BatchController::class => array(
                'parameters' => array(
                    'service' => 'BatchService'
                )
            ),
            BatchCollectionController::class => array(
                'parameters' => array(
                    'service' => 'BatchCollectionService'
                )
            ),
            'BatchService' => array(
                'parameters' => array(
                    'repository' => BatchETagStorage::class
                )
            ),
            'BatchCollectionService' => array(
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
            UserPreferenceETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => UserPreferenceRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_UserPreference_Shared_Entity'
                )
            ),
            UserPreferenceController::class => array(
                'parameters' => array(
                    'service' => 'UserPreferenceService'
                )
            ),
            UserPreferenceCollectionController::class => array(
                'parameters' => array(
                    'service' => 'UserPreferenceCollectionService'
                )
            ),
            'UserPreferenceService' => array(
                'parameters' => array(
                    'repository' => UserPreferenceETagStorage::class
                )
            ),
            'UserPreferenceCollectionService' => array(
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
            FilterService::class => array(
                'parameter' => array(
                    'filterStorage' => FilterCache::class,
                    'orderService' => 'OrderService',
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
            TemplateETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => TemplateRepository::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders',
                    'entityClass' => 'CG_Order_Template_Shared_Entity'
                )
            ),
            TemplateController::class => array(
                'parameters' => array(
                    'service' => 'TemplateService'
                )
            ),
            TemplateCollectionController::class => array(
                'parameters' => array(
                    'service' => 'TemplateCollectionService'
                )
            ),
            'TemplateService' => array(
                'parameters' => array(
                    'repository' => TemplateETagStorage::class
                )
            ),
            'TemplateCollectionService' => array(
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
                IncrementorInterface::class => Incrementor::class
            )
        )
    )
);
