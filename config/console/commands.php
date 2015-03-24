<?php
$files = [
    'failoverClient.php',
    'channel.php',
    'listing.php',
    'mongo.php',
    'retry.php',
    'ekm.php',
    'stock.php',
    'migrateMongoOrderDataToMysql.php',
    'migrateMongoOrderItemDataToMysql.php',
    'removeThenCorrectImportedProducts.php',
];

$commands = array();
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
