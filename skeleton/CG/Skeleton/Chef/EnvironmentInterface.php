<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Config;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp(Config $config);
}