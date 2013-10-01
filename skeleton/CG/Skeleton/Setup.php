<?php
namespace CG\Skeleton;

use Zend\Config\Config;

class Setup
{
    protected $config;

    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function run()
    {

    }
}