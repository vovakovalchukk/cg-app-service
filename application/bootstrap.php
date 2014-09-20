<?php
use CG\Log\FatalErrorHandler;
use Slim\Slim;
define('PROJECT_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

//Autoloading
require_once dirname(__DIR__) . DS .'vendor' . DS .'autoload.php';

//Application
$components = array(
    'config',
    'service_manager'
);
foreach ($components as $component) {
    require_once __DIR__ . DS . $component . DS .'bootstrap.php';
}

$di = $serviceManager->get('Di');
$app = $di->get(Slim::class);

$di->get(FatalErrorHandler::class);
