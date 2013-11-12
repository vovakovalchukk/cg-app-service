<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Console;

class Local extends Environment {

    protected $name = 'Local';
    protected $suffix = '.local';

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

            if (!empty($configuredHosts)) {
                $console->writeln('The following ips are in use');
                foreach ($configuredHosts as $host) {
                    $console->writeln('   * ' . $host['ip'] . ' => ' . $host['hostname']);
                }
                $console->writeln('Please remember other IPs may be in use, please confer with other developers before setting an ip address');
            }

            $ipAddress = $console->ask('What IP address would you like to access the vm for this node', '192.168.33.21');
        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function vagrantUp(Console $console)
    {
        passthru('vagrant up ' . $this->getConfig()->getNode());
    }

    public function vagrantSsh(Console $console)
    {
        passthru('vagrant ssh ' . $this->getConfig()->getNode());
    }

    public function vagrantReload(Console $console)
    {
        passthru('vagrant reload ' . $this->getConfig()->getNode());
    }

    public function vagrantHalt(Console $console)
    {
        passthru('vagrant halt ' . $this->getConfig()->getNode());
    }

    public function setupNode(Startup $console)
    {
        $node = $this->getConfig()->getNode();
        while (!$node) {
            $console->writeErrorStatus('No node set');
            $node = $console->ask('What node will this application go on');
        }
        $console->writeStatus('Application configured for node \'' . $node . '\'');
        $this->getEnvironmentConfig()->setNode($node);
    }
}
