<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\Module\ModuleInterface;
use CG\Skeleton\Console;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Module\BaseConfig;
use InvalidArgumentException;
use SplObjectStorage;
use CG\Skeleton\Console\Lists\Commands;
use CG\Skeleton\Module\Command\Enable;

abstract class AbstractModule implements ModuleInterface
{
    protected $console;

    public function __construct(Console $console)
    {
        $this->setConsole($console);
    }

    public function setConsole(Console $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function getName()
    {
        return $this->getModuleName() . ' Module';
    }

    abstract public function getConfigClass();

    public function run(Arguments $arguments, SkeletonConfig $config, BaseConfig $moduleConfig = null)
    {
        if (!is_a($moduleConfig, $this->getConfigClass())) {
            throw new InvalidArgumentException('$moduleConfig should be an instance of ' . $this->getConfigClass());
        }

        $commands = new SplObjectStorage();
        if ($this instanceof EnableInterface) {
            $commands->attach(new Enable($this, $moduleConfig));
        }
        while ($this->commandList($commands, $arguments, $config));
    }

    protected function commandList(SplObjectStorage $commands, Arguments $arguments, SkeletonConfig $config)
    {
        $commandList = new Commands($this->getConsole(), $commands);
        return $commandList->askAndRun($arguments, $config);
    }
}