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
    'orderCountsRoutes.php',
    'courier.php',
    'exchangeRate.php',
    'shipmentMetadata.php',
    'orderLink.php',
    'purchaseOrderRoutes.php',
    'reportRoutes.php',
    'ekmRoutes.php',
    'categoryRoutes.php'
];
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}
return $routes;
