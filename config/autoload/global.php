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
use Zend\EventManager\EventManager;
use Zend\Config\Config;

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
                'EventCollectionService' => EventService::class
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
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                'CG\Cache\ClientInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\ClientPipelineInterface' => 'CG\Cache\Client\RedisPipeline',
                'CG\Cache\KeyGeneratorInterface' => 'CG\Cache\KeyGenerator\Redis',
                'CG\Cache\Strategy\SerialisationInterface' => 'CG\Cache\Strategy\Serialisation\Serialize',
                'CG\Cache\Strategy\CollectionInterface' => 'CG\Cache\Strategy\Collection\Entities',
                'CG\Cache\Strategy\EntityInterface' => 'CG\Cache\Strategy\Entity\Standard',
                'CG\ETag\StorageInterface' => 'CG\ETag\Storage\Predis'
            )
        )
    )
);
