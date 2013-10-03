<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Console;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Console\ModuleList;

class Command implements CommandInterface
{
    protected $console;
    protected $modules;
    protected $defaults;

    public function __construct(Console $console)
    {
        $this->setConsole($console);
        $this->modules = new SplObjectStorage();
        $this->defaults = array();
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

    public function addModule(ModuleInterface $module)
    {
        $this->modules->attach($module);
        return $this;
    }

    public function getModules()
    {
        return $this->modules;
    }

    public function getName()
    {
        return 'Configure Modules';
    }

    public function run(Arguments $arguments, SkeletonConfig $config)
    {
        $moduleConfig = $config->get('Module', new Config($this->defaults, true));
        $config->offsetSet('Module', $moduleConfig);

        while ($this->moduleList($arguments, $config));
    }

    public function moduleList(Arguments $arguments, SkeletonConfig $config)
    {
        $moduleList = new ModuleList($this->getConsole(), $this->getModules());
        return $moduleList->askAndRun($arguments, $config);
    }
}