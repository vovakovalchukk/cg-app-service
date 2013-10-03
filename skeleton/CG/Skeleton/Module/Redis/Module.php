<?php
namespace CG\Skeleton\Module\Redis;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\DisableInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;

class Module extends AbstractModule implements EnableInterface, DisableInterface
{
    public function getModuleName()
    {
        return 'Redis';
    }

    public function enable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }

    public function disable(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(false);
    }
}