<?php
$files = [
    'failoverClient.php',
];

$commands = array();
foreach ($files as $file) {
    $command = require_once __DIR__ . '/commands/' . $file;
    $commands = array_merge($commands, $command);
}
return $commands;
