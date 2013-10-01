<?php
namespace CG\Skeleton;

use Zend\Config\Config;

interface Command
{
    public function getName();
    public function run(Arguments $arguments, Config $config);
}