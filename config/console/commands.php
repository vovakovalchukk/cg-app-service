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
    'migrateMongoUserChangeDataToMysql.php',
    'removeThenCorrectImportedProducts.php',
    'reAddInActionOrdersToGearman.php',
    'account.php',
    'currency.php',
    'migrateMongoOrderLabelDataToMysqlAndS3.php',
    'cache.php',
    'phinxMongoMigrations.php',
    'transaction.php',
    'settings.php'
];

$commands = array();
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
