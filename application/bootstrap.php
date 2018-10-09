<?php
use CG\Log\FatalErrorHandler;
use Slim\Slim;
use CG\Profiling\Profiler;

define('PROJECT_ROOT', dirname(__DIR__));
define('DS', DIRECTORY_SEPARATOR);

Profiler::startProfiling(APPLICATION, [], [
    Profiler::MODE_BASIC => 70,
    Profiler::MODE_PROFILE => 29,
    Profiler::MODE_TRACE => 1
]);

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
