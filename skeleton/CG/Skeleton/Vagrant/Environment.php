<?php
namespace CG\Skeleton\Vagrant;

class Environment
{
    protected $path;
    protected $data;

    public function __construct($path, $environmentName)
    {
        $this->path = $path;
        $this->data = array("current_environment" => $environmentName);
        $this->load();
    }

    public function getData()
    {
        return $this->data;
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

    public function setEnvironment($environmentName)
    {
        $this->data['current_environment'] = $environmentName;
    }

    public function save()
    {
        var_dump($this->data);
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}