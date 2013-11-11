<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Console\Startup;
use CG\Skeleton\Config as SkeletonConfig;

abstract class Environment implements EnvironmentInterface {

    protected $console;
    protected $skeletonConfig;
    protected $environmentConfig;

    public function __construct(Startup $console, SkeletonConfig $config)
    {
        $this->skeletonConfig = $config;
        $this->setEnvironmentConfig();
        $this->setConsole($console);
    }

    public function setConsole(Startup $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function getEnvironmentConfig()
    {
        return $this->environmentConfig;
    }

    protected function setEnvironmentConfig()
    {
        $environmentConfig = $this->skeletonConfig->get('Environment', new Config(array(), true));
        $this->environmentConfig = $environmentConfig->get($this->skeletonConfig->getEnvironment(), new Config(array(), true));

        $environmentConfig->offsetSet($this->skeletonConfig->getEnvironment(), $this->environmentConfig);
        $this->skeletonConfig->offsetSet('Environment', $environmentConfig);
        return $this;
    }

}