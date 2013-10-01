<?php
use Zend\Config\Config;
use Zend\Config\Factory;

$config = new Config(
    Factory::fromFiles(
        glob('config/autoload/{,*.}{global,local}.php', GLOB_BRACE)
    )
);

return $config;