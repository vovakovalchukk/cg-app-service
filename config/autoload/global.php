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

use Zend\Config\Config;

//Usage
use CG\Usage\Storage\Db as UsageDb;
use CG\Usage\Aggregate\Storage\Db as UsageAggregateDb;
use CG\Usage\Storage\Redis as UsageRedis;
use CG\Usage\Repository as UsageRepository;
use CG\Usage\StorageInterface as UsageStorageInterface;

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Di\Di' => function($serviceManager) {
                $configuration = $serviceManager->get('config');

                $definition = new CG\Di\Definition\RuntimeDefinition(null, require dirname(dirname(__DIR__)) .  '/vendor/composer/autoload_classmap.php');
                $definitionList = new Zend\Di\DefinitionList([$definition]);
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
                'Di' => 'Zend\Di\Di',
                'config' => Config::class
             ),
            UsageDb::class=> [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageAggregateDb::class=> [
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
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                UsageStorageInterface::class => UsageRepository::class
            )
        )
    )
);
