<?php
namespace CG\Skeleton\Chef;

use CG\Skeleton\Chef\Hosts\Host;

class Hosts
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    protected function load($reload = false)
    {
        if (is_array($this->data) && $reload == false) {
            return;
        }

        $this->data = array();

        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($this->path), true);
        if (!is_array($jsonData)) {
            return;
        }

        foreach ($jsonData as $host => $hostData) {
            $this->data[$host] = new Host($hostData);
        }
    }

    public function getHost($host)
    {
        $this->load();
        if (!isset($this->data[$host])) {
            $this->data[$host] = new Host();
        }

        $hosts =& $this->data[$host];
        return $hosts;
    }

    public function getIpsInUse()
    {
        $this->load();
        $ips = array();
        foreach ($this->data as $hostKey => $host) {
            $ip = $host->getIp();
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                continue;
            }
            $ips[$hostKey] = $ip;
        }
        return $ips;
    }

    public function save()
    {
        $this->load();
        $data = array();
        foreach ($this->data as $hostKey => $host) {
            $data[$hostKey] = $host->getArrayCopy();
        }
        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT));
    }
}