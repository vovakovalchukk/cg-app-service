<?php
namespace CG\Skeleton\Chef;

use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Vagrant\NodeData\Node;

class Config extends ZendConfig
{
    const IP = 'IP';
    const HOSTNAME = 'hostname';
    const DOMAIN = 'channelgrabber.com';

    public function getIp()
    {
        return $this->get(static::IP);
    }

    public function setIp($ip)
    {
        $this->offsetSet(static::IP, $ip);
        return $this;
    }

    public function getHostname()
    {
        return $this->get(static::HOSTNAME, $this->getAppName() . '.' . static::DOMAIN);
    }

    public function setHostname($hostname)
    {
        $this->offsetSet(static::HOSTNAME, $hostname);
        return $this;
    }
}