<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Module\BaseConfig;

class Module extends AbstractModule implements EnableInterface
{
    public function getModuleName()
    {
        return 'Db';
    }

    public function getConfigClass()
    {
        return 'CG\Skeleton\Module\BaseConfig';
    }

    public function enable(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        $moduleConfig->setEnabled(true);
    }
}