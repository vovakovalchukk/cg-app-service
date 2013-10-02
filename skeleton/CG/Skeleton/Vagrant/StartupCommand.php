<?php
namespace CG\Skeleton\Vagrant;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\Vagrant\NodeData;
use CG\Skeleton\Vagrant\NodeData\Node;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;

    const NODE_DATA_PATH = 'data/nodeData.json';
    const DEFAULT_RAM = '768';

    protected $console;
    protected $defaults;

    public function __construct(Startup $console)
    {
        $this->setConsole($console);
        $this->defaults = array();
    }

    public function setConsole(Startup $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    protected function runCommands(Arguments $arguments, SkeletonConfig $config)
    {
        $vagrantConfig = $config->get('Vagrant', new Config($this->defaults, true));
        $this->saveNodeData($config, $vagrantConfig);
        $config->offsetSet('Vagrant', $vagrantConfig);
    }

    protected function saveNodeData(SkeletonConfig $config, Config $vagrantConfig)
    {
        $nodeData = new NodeData(static::NODE_DATA_PATH);
        $node = $nodeData->getNode($config->getNode());

        $this->setVmRam($nodeData, $node, $config, $vagrantConfig);
        $this->setVmIp($nodeData, $node, $config, $vagrantConfig);

        $nodeData->save();

        exec(
            'git add ' . static::NODE_DATA_PATH . ';'
            . ' git commit -m "SKELETON: Updated node data for ' . $config->getNode() . '" --only -- ' . static::NODE_DATA_PATH
        );
    }

    protected function setVmRam(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {
        $vmRam = $vagrantConfig->getVmRam();
        while (!$vmRam) {
            $this->getConsole()->writeErrorStatus('VM Ram is not set');
            $vmRam = $this->getConsole()->ask('Please enter amount of Ram to assign VM', static::DEFAULT_RAM);
        }

        $this->getConsole()->writeStatus('VM Ram set as \'' . $vmRam . '\'');
        $node->setVmRam($vmRam);
        $vagrantConfig->setVmRam($vmRam);
    }

    protected function setVmIp(NodeData $nodeData, Node $node, SkeletonConfig $config, Config $vagrantConfig)
    {

    }
}