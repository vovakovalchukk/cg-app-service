<?php
define('DS', DIRECTORY_SEPARATOR);

//Autoloading
require_once __DIR__ . DS .'autoload.php';
require_once dirname(__DIR__) . DS .'vendor' . DS .'autoload.php';

//Application
$components = array(
    'config',
    'service_manager'
);
foreach ($components as $component) {
    require_once __DIR__ . DS . $component . DS .'bootstrap.php';
}
