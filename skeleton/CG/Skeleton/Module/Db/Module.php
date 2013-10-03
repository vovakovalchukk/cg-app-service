<?php
namespace CG\Skeleton\Module\Db;

use CG\Skeleton\Module\AbstractModule;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Module\BaseConfig;

class Module extends AbstractModule implements EnableInterface, ConfigureInterface
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
        if ($moduleConfig->isEnabled()) {
            return;
        }
        $this->configure($arguments, $config, $moduleConfig);
        $moduleConfig->setEnabled(true);
    }

    public function configure(Arguments $arguments, Config $config, BaseConfig $moduleConfig)
    {
        
    }
}