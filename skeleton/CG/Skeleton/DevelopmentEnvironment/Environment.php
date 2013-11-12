<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Chef\StartupCommand;
use CG\Skeleton\Chef\Hosts;

abstract class Environment implements EnvironmentInterface {

    protected $skeletonConfig;
    protected $environmentConfig;

    public function __construct(SkeletonConfig $config)
    {
        $this->setConfig($config);
        $this->setEnvironmentConfig();
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

    public function getConfig()
    {
        return $this->skeletonConfig;
    }

    public function setConfig(SkeletonConfig $config)
    {
        $this->skeletonConfig = $config;
        return $this;
    }

    protected function getHosts()
    {
        $hostsFile = StartupCommand::HOSTS . strtolower($this->getName()) . '.json';
        $hosts = new Hosts($hostsFile, $this->getName());
        return $hosts->getData()['hosts'];
    }

}