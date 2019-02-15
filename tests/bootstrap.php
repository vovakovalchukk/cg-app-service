<?php
define('APPLICATION', 'cg_app');
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/vendor/channelgrabber/stdlib/tests/external-bootstrap.php';
require_once dirname(__DIR__) . '/application/bootstrap.php';
$GLOBALS['di'] = $di;
