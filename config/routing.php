<?php
$routes = [];
$files = [
    'routes.php',
    'order.php',
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
    'categoryRoutes.php',
    'listingTemplate.php',
    'supplierRoutes.php',
    'orderItemRefund.php',
];
foreach ($files as $file) {
    $route = require_once __DIR__.'/routes/'.$file;
    $routes = array_merge($routes, $route);
}
return $routes;
