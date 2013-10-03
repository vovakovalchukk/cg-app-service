<?php
namespace CG\Skeleton\Module\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Module\ConfigureInterface;
use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Console;

class Enable implements CommandInterface
{
    protected $enableCommand;
    protected $moduleConfig;

    public function __construct(EnableInterface $enableCommand, BaseConfig $moduleConfig)
    {
        $this->setEnableCommand($enableCommand)->setModuleConfig($moduleConfig);
    }

    public function setEnableCommand(EnableInterface $enableCommand)
    {
        $this->enableCommand = $enableCommand;
        return $this;
    }

    public function getEnableCommand()
    {
        return $this->enableCommand;
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
        return 'Enable'
            . ($this->getEnableCommand() instanceof ConfigureInterface ? ' & Configure' : '')
            . ' Module';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $this->getEnableCommand()->enable($arguments, $config, $this->getModuleConfig());
        if ($this->getEnableCommand() instanceof ConfigureInterface) {
            $this->getEnableCommand()->configure($arguments, $config, $this->getModuleConfig());
        }
    }
}