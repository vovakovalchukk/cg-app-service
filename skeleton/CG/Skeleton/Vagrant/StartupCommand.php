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
        $vmIp = $vagrantConfig->getVmIp();
        $vmIps = $nodeData->getIpsInUse();

        while (!filter_var($vmIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->getConsole()->writeErrorStatus('VM ip is not set or is invalid');

            if (!empty($vmIps)) {
                $this->getConsole()->writeln('The following ips are in use');
                foreach ($vmIps as $nodeName => $ip) {
                    $this->getConsole()->writeln('   * ' . $ip . ' => ' . $nodeName);
                }
                $this->getConsole()->writeln('Please remember other ips may be in use, please confer with other developers before setting an ip address');
            }

            $vmIp = $this->getConsole()->ask('What local ip address would you like to access the vm for this node', '192.168.33.21');
        }

        $this->getConsole()->writeStatus('VM ip set as \'' . $vmIp . '\'');
        $node->setVmIp($vmIp);
        $vagrantConfig->setVmIp($vmIp);

        $this->getConsole()->writeStatus(
            'Saving VM ip to /etc/hosts '
            . Startup::COLOR_PURPLE . '(You may be prompted for your password)' . Startup::COLOR_RESET
        );

        exec(
            'grep -q -e "' . $vmIp . ' ' . $config->getHostname() . '.local" /etc/hosts'
            . ' || echo "' . $vmIp . ' ' . $config->getHostname() . '.local" | sudo tee -a /etc/hosts'
        );

        $this->getConsole()->writeStatus('VM ip saved to /etc/hosts');
    }
}