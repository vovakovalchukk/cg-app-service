<?php
use Slim\Slim;

$di = $serviceManager->get('Di');
$app = $serviceManager->get(Slim::class);

$routes = array();
$files = [
    'routes.php'
];
 
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}