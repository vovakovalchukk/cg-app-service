<?php
namespace CG\Skeleton\Chef;

class Node
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
        $this->data = array(
            'run_list' => array()
        );
        $this->load();
    }

    protected function load()
    {
        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode($this->path, true);
        if (!is_array($jsonData)) {
            return;
        }

        $this->data = array_merge($this->data, $jsonData);
    }

    public function addToRunList($run)
    {
        if (isset(array_flip($this->data['run_list'])[$run])) {
            return $this;
        }

        $this->data['run_list'][] = $run;
        return $this;
    }

    public function setKey($key, $value)
    {
        $data =& $this->data;
        foreach (explode('.', $key) as $currentKey) {
            $data =& $data[$currentKey];
        }
        $data = $value;
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}