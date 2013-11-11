<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Console\Startup;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp(Startup $console);
    public function setupHostname(Startup $console);
}