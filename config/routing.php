<?php
$routes = array();
$files = [
    'routes.php',
    'settings.php'
];
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}
return $routes;