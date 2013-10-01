<?php
chdir(dirname(__DIR__));

if (!is_file('composer.phar')) {
    passthru('curl -sS https://getcomposer.org/installer | php');
}

if (!is_file('vendor/autoload.php')) {
    passthru('php composer.phar install');
}

$autoloader = require 'vendor/autoload.php';
$autoloader->set('CG\Skeleton', __DIR__);