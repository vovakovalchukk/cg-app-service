<?php
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\Di\DiAbstractServiceFactory;

if (!isset($config)) {
    $config = require_once dirname(__DIR__) . '/config/bootstrap.php';
}

$serviceManager = new ServiceManager(
    new Config(
        isset($config['service_manager']) ? $config['service_manager'] : array()
    )
);

$serviceManager->setService('Zend\Config\Config', $config);
$serviceManager->setService('Config', $config->toArray());

$serviceManager->addInitializer(function($instance) use ($serviceManager) {
    if ($instance instanceof ServiceManagerAwareInterface) {
        $instance->setServiceManager($serviceManager);
    }
});

$serviceManager->addInitializer(function($instance) use ($serviceManager) {
    if ($instance instanceof ServiceLocatorAwareInterface) {
        $instance->setServiceLocator($serviceManager);
    }
});

$serviceManager->setService('ServiceManager', $serviceManager);
$serviceManager->setAlias('Zend\ServiceManager\ServiceLocatorInterface', 'ServiceManager');
$serviceManager->setAlias('Zend\ServiceManager\ServiceManager', 'ServiceManager');

$serviceManager->addAbstractFactory(
    new DiAbstractServiceFactory(
        $serviceManager->get('Di'),
        DiAbstractServiceFactory::USE_SL_BEFORE_DI
    )
);