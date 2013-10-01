<?php
namespace CG\Skeleton;

use Zend\Config\Config as ZendConfig;

class Config extends ZendConfig
{
    const PROJECT_BASE_PATH = 'PROJECT_BASE_PATH';
    const INFRASTRUCTURE_PATH = 'INFRASTRUCTURE_PATH';
    const INFRASTRUCTURE_NAME = 'CGInfrastructure-V4';
    const INFRASTRUCTURE_REPOSITORY = 'git@bitbucket.org:channelgrabber/cginfrastructure-v4.git';
    const INFRASTRUCTURE_BRANCH = 'origin/master';
    const BRANCH = 'BRANCH';
    const NODE = 'NODE';
    const APP_NAME = 'APP_NAME';
    const HOST_NAME = 'HOST_NAME';
    const DOMAIN = 'channelgrabber.com';
    const VM_PATH = 'VM_PATH';

    public function getProjectBasePath()
    {
        $projectBasePath = $this->get(static::PROJECT_BASE_PATH);
        if ($projectBasePath) {
            return rtrim($projectBasePath, '/');
        }

        if (isset($_SERVER[static::PROJECT_BASE_PATH])) {
            return rtrim($_SERVER[static::PROJECT_BASE_PATH], '/');
        }

        return '';
    }

    public function getInfrastructurePath()
    {
        $infrastructurePath = $this->get(static::INFRASTRUCTURE_PATH);
        if ($infrastructurePath) {
            return $infrastructurePath;
        }
        return $this->getProjectBasePath() . '/cginfrastructure-v4';
    }

    public function getBranch()
    {
        return $this->get(static::BRANCH);
    }

    public function getNode()
    {
        return $this->get(static::NODE);
    }

    public function getAppName()
    {
        return $this->get(static::APP_NAME);
    }

    public function getHostname()
    {
        return $this->get(static::HOST_NAME, $this->getAppName() . '.' . static::DOMAIN);
    }

    public function getVmPath()
    {
        return rtrim($this->get(static::VM_PATH), '/');
    }
}