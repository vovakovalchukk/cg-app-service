<?php
$commands = array();
$files = [
    'downloadOrders.php'
];
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;