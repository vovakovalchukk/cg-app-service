#!/usr/bin/php
<?php
require 'skeleton/bootstrap.php';
$setup = $di->get('CG\Skeleton\Setup');
$setup->run();
Zend\Config\Factory::toFile(SKELETON_CONFIG, $setup->getConfig());