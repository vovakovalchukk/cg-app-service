#!/usr/bin/php
<?php
require 'skeleton/bootstrap.php';

$config = array();
if (is_file(SKELETON_CONFIG)) {
    $config = Zend\Config\Factory::fromFile(SKELETON_CONFIG);
}

$setup = new CG\Skeleton\Setup(
    new Zend\Config\Config($config, true)
);
$setup->run();
Zend\Config\Factory::toFile(SKELETON_CONFIG, $setup->getConfig());