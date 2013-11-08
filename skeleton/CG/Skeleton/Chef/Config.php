<?php
namespace CG\Skeleton\Chef;

use Zend\Config\Config as ZendConfig;
use CG\Skeleton\Vagrant\NodeData\Node;

class Config extends ZendConfig
{
    public function getHostname()
    {
        return $this->get(Node::VM_RAM);
    }

    public function setHostname($hostname)
    {
        $this->offsetSet(Node::VM_RAM, $vmRam);
        return $this;
    }
}