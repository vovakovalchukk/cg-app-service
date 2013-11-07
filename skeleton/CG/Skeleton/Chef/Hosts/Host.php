<?php
namespace CG\Skeleton\Chef\Hosts;

use ArrayObject;

class Host extends ArrayObject
{
    const HOSTNAME = 'hostname';
    const IP = 'ip';

    public function getHostname()
    {
        return $this->offsetExists(static::HOSTNAME) ? $this->offsetGet(static::HOSTNAME) : null;
    }

    public function setHostname($hostname)
    {
        $this->offsetSet(static::HOSTNAME, $hostname);
        return $this;
    }

    public function getIp()
    {
        return $this->offsetExists(static::IP) ? $this->offsetGet(static::IP) : null;
    }

    public function setIp($ip)
    {
        $this->offsetSet(static::IP, $ip);
        return $this;
    }
}