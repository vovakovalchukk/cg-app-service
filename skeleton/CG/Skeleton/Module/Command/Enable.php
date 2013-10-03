<?php
namespace CG\Skeleton\Module\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Module\EnableInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class Enable implements CommandInterface
{
    protected $enableCommand;

    public function __construct(EnableInterface $enableCommand)
    {
        $this->setEnableCommand($enableCommand);
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

    public function getName()
    {
        return 'Enable Module';
    }

    public function run(Arguments $arguments, Config $config)
    {
        $this->getEnableCommand()->enable($arguments, $config);
    }
}