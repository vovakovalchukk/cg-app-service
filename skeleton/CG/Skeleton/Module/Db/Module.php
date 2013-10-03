<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\ModuleInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;

class Module implements ModuleInterface
{
    public function getName()
    {
        return 'Db Module';
    }

    public function run(Arguments $arguments, SkeletonConfig $config)
    {
        
    }
}