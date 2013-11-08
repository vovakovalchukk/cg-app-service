<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Console\Startup;
use CG\Skeleton\Config;

abstract class AbstractEnvironment implements EnvironmentInterface {

    protected $console;
    protected $skeletonConfig;
    protected $environmentConfig;

    public function __construct(Startup $console, Config $config)
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