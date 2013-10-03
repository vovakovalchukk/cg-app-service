<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Module\BaseConfig;

interface EnableInterface
{
    public function enable(Arguments $arguments, Config $config, BaseConfig $moduleConfig);
}