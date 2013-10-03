<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

interface EnableInterface
{
    public function enable(Arguments $arguments, Config $config);
}