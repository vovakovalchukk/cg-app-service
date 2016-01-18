<?php
$routes = [];
$files = [
    'routes.php',
    'templateRoutes.php',
    'settingsRoutes.php',
    'productRoutes.php',
    'stockRoutes.php',
    'listingRoutes.php',
    'locationRoutes.php',
    'orderCountsRoutes.php'
];
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}
return $routes;