<?php
namespace CG\Skeleton\Chef;

class Host
{
    protected $hostname;
    protected $ip;

    public function __construct()
    {

    }

    protected function load()
    {
//        if (!is_file($this->path)) {
//            return;
//        }
//
//        $jsonData = json_decode(file_get_contents($this->path), true);
//        if (!is_array($jsonData)) {
//            return;
//        }
//
//        $this->data = array_merge($this->data, $jsonData);
    }

    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
        return $this;
    }

    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    public function save()
    {

    }
}