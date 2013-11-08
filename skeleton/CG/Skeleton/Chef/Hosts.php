<?php
namespace CG\Skeleton\Chef;

class Hosts
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
        $this->load();
    }

    protected function load()
    {
        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($this->path), true);
        if (!is_array($jsonData)) {
            return;
        }

        $this->data = array_merge($this->data, $jsonData);
    }

    public function setHost($host, $hostname, $ip)
    {
        $this->data[$host] = array(
            'hostname' => $hostname,
            'ip' => $ip
        );
    }

    public function getIpsInUse()
    {

    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}