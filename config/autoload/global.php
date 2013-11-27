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
use Zend\Di\Di;
use CG\App\Service\Storage\Db as ServiceDb;
use CG\App\Service\Storage\Cache as ServiceCache;
use CG\App\Service\Repository as ServiceRepository;
use CG\App\Service\Service as ServiceService;
use CG\App\Service\Event\Service as EventService;
use CG\App\Service\Event\Storage\Db as EventDb;
use CG\App\Service\Event\Storage\Cache as EventCache;
use CG\App\Service\Event\Repository as EventRepository;
use CG\Order\Service\Service as OrderService;
use CG\Order\Shared\Repository as OrderRepository;
use CG\Order\Service\Storage\Cache as OrderCacheStorage;
use CG\Order\Service\Storage\ElasticSearch as OrderElasticSearchStorage;
use CG\Order\Service\Storage\Persistent as OrderPeristentStorage;
use CG\Order\Service\Storage\Persistent\Db as OrderPeristentDbStorage;
use CG\Order\Service\Note\Service as NoteService;
use CG\Order\Shared\Note\Repository as NoteRepository;
use CG\Order\Service\Note\Storage\Cache as NoteCacheStorage;
use CG\Order\Service\Note\Storage\Db as NoteDbStorage;
use Zend\Config\Config;
use Zend\EventManager\EventManager;
use CG\Slim\Stdlib\Http\Headers;
use CG\Controllers\Order\Order as OrderController;
use CG\Controllers\Order\Order\Collection as OrderCollectionController;
use CG\ETag\Storage\Predis as OrderPredis;
use CG\Order\Service\Storage\ETag as OrderETagStorage;
use CG\Controllers\Order\Note as NoteController;
use CG\Controllers\Order\Note\Collection as NoteCollectionController;
use CG\ETag\Storage\Predis as NotePredis;
use CG\Order\Service\Note\Storage\ETag as NoteETagStorage;
use Slim\Http\Headers as SlimHttpHeaders;

return array(
    'service_manager' => array(
        'factories' => array(
            Di::Class => function($serviceManager) {
                $configuration = $serviceManager->get('Config');

                $im = new Zend\Di\InstanceManager();
                $di = new Zend\Di\Di(null, $im, new Zend\Di\Config(
                    isset($configuration['di']) ? $configuration['di'] : array()
                ));

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, 'Zend\Db\Adapter\Adapter');
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, 'Di');
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
                'ServiceDbRepo' => ServiceRepository::class,
                'ServiceDbStorage' => ServiceDb::class,
                'ServiceCacheRepo' => ServiceRepository::class,
                'EventDbRepo' => EventRepository::class,
                'EventDbStorage' => EventDb::class,
                'EventCacheRepo' => EventRepository::class,
                'config' => Config::class,
                'OrderService' => OrderService::class,
                'OrderCollectionService' => OrderService::class,
                'NoteService' => NoteService::class,
                'NoteCollectionService' => NoteService::class
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
            'ServiceDbRepo' => array(
                'parameter' => array(
                    'storage' => 'ServiceDbStorage'
                )
            ),
            'ServiceDbStorage' => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            'ServiceCacheRepo' => array(
                'parameter' => array(
                    'storage' => ServiceCache::class,
                    'repository' => 'ServiceDbRepo'
                )
            ),
            ServiceService::class => array(
                'parameter' => array(
                    'repository' => 'ServiceCacheRepo'
                )
            ),
            EventService::class => array(
                'parameter' => array(
                    'repository' => 'EventCacheRepo'
                )
            ),
            'EventDbRepo' => array(
                'parameter' => array(
                    'storage' => 'EventDbStorage'
                )
            ),
            'EventDbStorage' => array(
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
            OrderRepository::class => array(
                'parameter' => array(
                    'storage' => OrderCacheStorage::class,
                    'repository' => OrderPeristentStorage::class
                )
            ),
            'OrderService' => array(
                'parameters' => array(
                    'repository' => OrderETagStorage::class,
                    'storage' => OrderElasticSearchStorage::class,
                    'noteService' => 'NoteService'
                )
            ),
            'OrderCollectionService' => array(
                'parameters' => array(
                    'repository' => OrderRepository::class,
                    'storage' => OrderElasticSearchStorage::class
                )
            ),
            OrderETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => OrderRepository::class,
                    'eTagStorage' => OrderPredis::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders'
                )
            ),
            OrderPredis::class => array (
                'parameter' => array (
                    'entityClass' => function() { return 'CG_Order_Shared_Entity'; }
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
            OrderPeristentDbStorage::class => array(
                'parameter' => array(
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                )
            ),
            NoteETagStorage::class => array (
                'parameter' => array(
                    'entityStorage' => NoteRepository::class,
                    'eTagStorage' => NotePredis::class,
                    'requestHeaders' => 'RequestHeaders',
                    'responseHeaders' => 'ResponseHeaders'
                )
            ),
            NotePredis::class => array (
                'parameter' => array (
                    'entityClass' => function() { return 'CG_Order_Note_Shared_Entity'; }
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
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                'CG\Cache\ClientInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\ClientPipelineInterface' => 'CG\Cache\Client\RedisPipeline',
                'CG\Cache\KeyGeneratorInterface' => 'CG\Cache\KeyGenerator\Redis',
                'CG\Cache\Strategy\SerialisationInterface' => 'CG\Cache\Strategy\Serialisation\Serialize',
                'CG\Cache\Strategy\CollectionInterface' => 'CG\Cache\Strategy\Collection\Entities',
                'CG\Cache\Strategy\EntityInterface' => 'CG\Cache\Strategy\Entity\Standard',
                \MongoClient::class => "mongodb"
            )
        )
    )
);
