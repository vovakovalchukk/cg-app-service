<?php
namespace CG\Skeleton\Vagrant;

class Environment
{
    protected $path;
    protected $data;

    public function __construct($path, $environmentName)
    {
        $this->path = $path;
        $this->data = array();
        $this->setEnvironment($environmentName);
    }

    public function setEnvironment($environmentName)
    {
        var_dump($environmentName);
        $this->data['current_environment'] = $environmentName;
    }

    public function save()
    {
        var_dump($this->data);
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}