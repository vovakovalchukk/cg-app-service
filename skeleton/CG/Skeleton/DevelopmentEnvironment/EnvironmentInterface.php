<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;

interface EnvironmentInterface
{
    public function getName();

    public function setupIp();
}