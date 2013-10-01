<?php
chdir(dirname(__DIR__));

define('SKELETON_CONFIG', __DIR__ . '/config.xml');

if (!is_file('composer.phar')) {
    passthru('curl -sS https://getcomposer.org/installer | php');
}

if (!is_file('vendor/autoload.php')) {
    passthru('php composer.phar install');
}

$autoloader = require 'vendor/autoload.php';
$autoloader->set('CG\Skeleton', __DIR__);

$commands = require_once('commands.php');