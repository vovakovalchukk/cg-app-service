<?php
$commands = array();
$files = [
    'channel.php',
    'mongo.php',
    'retry.php',
    'stock.php'
];
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
