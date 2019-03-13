<?php

use CG\Log\FatalErrorHandler;
use CG\Profiling\Service as Profiler;
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

$_GET['_tideways'] = ['method' => 'GET', 'time' => strtotime('+1 hour'), 'user' => '13417'];
$vars = $_GET['_tideways'];
$_GET['_tideways']['hash'] = hash_hmac('sha256', 'method=' . $vars['method'] . '&time=' . $vars['time'] . '&user=' . $vars['user'], md5('O8rHgRfEvwD2RxPX'));

Profiler::startProfiling(APPLICATION, [], [
    Profiler::MODE_BASIC => 70,
    Profiler::MODE_PROFILE => 29,
    Profiler::MODE_TRACE => 1
]);

$di = $serviceManager->get('Di');
$app = $di->get(Slim::class);

$di->get(FatalErrorHandler::class);
