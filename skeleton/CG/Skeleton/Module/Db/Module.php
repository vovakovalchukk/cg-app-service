<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\ModuleInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use InvalidArgumentException;

class Module implements ModuleInterface
{
    public function getName()
    {
        return 'Db Module';
    }

    public function getModuleName()
    {
        return __CLASS__;
    }

    public function run(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig = null)
    {
        if (!($moduleConfig instanceof BaseConfig)) {
            throw new InvalidArgumentException('$moduleConfig should be an instance of CG\Skeleton\Module\BaseConfig');
        }
    }
}