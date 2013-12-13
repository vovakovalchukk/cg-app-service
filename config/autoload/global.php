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

return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\Di\Di' => function($serviceManager) {
                $configuration = $serviceManager->get('Config');

                $definition = new CG\Di\Definition\RuntimeDefinition(null, require 'vendor/composer/autoload_classmap.php');
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
            'CG\RestExample\Service' => array(
                'parameter' => array(
                    'Repository' => 'CG\RestExample\Repository',
                    "Mapper" => 'CG\RestExample\Mapper'
                )
            ),
            'preferences' => array(
                'Zend\Di\LocatorInterface' => 'Zend\Di\Di',
                'CG\RestExample\ServiceInterface' => 'CG\RestExample\Service',
            )
        )
    )
);
