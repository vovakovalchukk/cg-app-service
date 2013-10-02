<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\Vagrant\NodeData\Node;

class NodeData
{
    protected $path;
    protected $data;

    public function __construct($path)
    {
        $this->path = $path;
        $this->data = array();
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

        foreach ($jsonData as $node => $nodeData) {
            $this->data[$node] = new Node($nodeData);
        }
    }

    public function getNode($node)
    {
        if (!isset($this->data[$node])) {
            $this->data[$node] = new Node();
        }

        $nodeData =& $this->data[$node];
        return $nodeData;
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}