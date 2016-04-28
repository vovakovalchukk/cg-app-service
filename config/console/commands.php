<?php
$files = [
    'ad_hoc.php',
    'failoverClient.php',
    'channel.php',
    'listing.php',
    'mongo.php',
    'retry.php',
    'ekm.php',
    'stock.php',
    'order.php',
    'migrateMongoOrderDataToMysql.php',
    'migrateMongoOrderItemDataToMysql.php',
    'removeThenCorrectImportedProducts.php',
    'reAddInActionOrdersToGearman.php',
    'account.php',
];

$commands = array();
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
