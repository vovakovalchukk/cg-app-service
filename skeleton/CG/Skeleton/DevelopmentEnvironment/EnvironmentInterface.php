<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;

interface EnvironmentInterface
{
    public function getName();
    public function getSuffix();

    public function setupIp(Startup $console);

    public function setupNode(Startup $console);

    public function vagrantUp(Console $console);
    public function vagrantSsh(Console $console);
    public function vagrantReload(Console $console);
    public function vagrantHalt(Console $console);
}