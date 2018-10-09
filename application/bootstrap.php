<?php

use CG\Log\FatalErrorHandler;
use CG\Profiling\Profiler;
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

Profiler::startProfiling('cg_app_dev', [], [
    Profiler::MODE_BASIC => 70,
    Profiler::MODE_PROFILE => 29,
    Profiler::MODE_TRACE => 1
]);

$di = $serviceManager->get('Di');
$app = $di->get(Slim::class);

$di->get(FatalErrorHandler::class);
