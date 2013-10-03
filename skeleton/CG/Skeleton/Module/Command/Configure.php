<?php
namespace CG\Skeleton\Module\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class Configure implements CommandInterface
{
    protected $configureCommand;
    protected $moduleConfig;

    public function __construct(ConfigureInterface $configureCommand, BaseConfig $moduleConfig)
    {
        $this->setConfigureCommand($configureCommand)->setModuleConfig($moduleConfig);
    }

    public function setConfigureCommand(ConfigureInterface $configureCommand)
    {
        $this->configureCommand = $configureCommand;
        return $this;
    }

    public function getConfigureCommand()
    {
        return $this->configureCommand;
    }

    public function setModuleConfig(BaseConfig $moduleConfig)
    {
        $this->moduleConfig = $moduleConfig;
        return $this;
    }

    public function getModuleConfig()
    {
        return $this->moduleConfig;
    }

    public function getName()
    {
        return 'Configure Module';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $this->getConfigureCommand()->configure($arguments, $config, $this->getModuleConfig());
    }
}