<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;

class Dual extends Environment {

    protected $name = 'Dual';
    protected $suffix = '.local';

    protected $environmentNodes = array('infrastructure', 'services', 'frontend');

    public function getName()
    {
        return $this->name;
    }

    public function getSuffix()
    {
        return $this->suffix;
    }

    public function setupIp(Startup $console)
    {
        $ipAddress = $this->getEnvironmentConfig()->getIp();
        $configuredHosts = $this->getHosts();

        while (!filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $console->writeErrorStatus('IP address is not set or is invalid');
            $chosenNode = $console->askWithOptions(
                'Which node would you like to run your app on?',
                $this->environmentNodes,
                $this->environmentNodes[1]
            );
            $ipAddress = $configuredHosts[$chosenNode]['ip'];
        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function vagrantUp(Console $console)
    {
        foreach($this->environmentNodes as $node) {
            passthru('vagrant up ' . $node);
        }
    }

    public function vagrantSsh(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to connect to',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant ssh ' . $nodeChoice);
    }

    public function vagrantReload(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to restart',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant reload ' . $nodeChoice);
    }

    public function vagrantHalt(Console $console)
    {
        $nodeChoice = $console->askWithOptions(
            'Which node would you like to stop',
            $this->environmentNodes,
            $this->environmentNodes[1]
        );
        passthru('vagrant halt ' . $nodeChoice);
    }

    public function setupNode(Startup $console)
    {
        echo "hi\n";
    }
}
