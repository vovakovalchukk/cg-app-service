<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;

trait CommandTrait
{
    public function run(Arguments $arguments, SkeletonConfig $config)
    {
        $cwd = getcwd();
        chdir($config->getInfrastructurePath() . '/tools/chef');
        exec('git checkout ' . $config->getBranch() . ' 2>&1;');
        $this->runCommands($arguments, $config);
        chdir($cwd);
    }

    abstract protected function runCommands(Arguments $arguments, SkeletonConfig $config);
}