<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp();
}