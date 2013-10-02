<?php
namespace CG\Skeleton\Module;

use CG\Skeleton\CommandInterface;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;

class Command implements CommandInterface
{
    protected $modules;
    protected $defaults;

    public function __construct()
    {
        $this->modules = new SplObjectStorage();
        $this->defaults = array();
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
    }
}