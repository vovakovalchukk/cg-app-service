<?php
namespace CG\Skeleton\DevelopmentEnvironment\Environment;

use CG\Skeleton\DevelopmentEnvironment\Environment;
use CG\Skeleton\Console\Startup;
use CG\Skeleton\Chef\StartupCommand;
use CG\Skeleton\Chef\Hosts;

class Local extends Environment {

    public function getName()
    {
        return 'Local';
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


//        $console->writeln('IP addresses already in use:');
//        foreach ($configuredHosts as $host) {
//            $this->getConsole()->writeln('   * ' . $host);
//        }
//
//        $ip = $this->getEnvironmentConfig()->getIp();
//        while (!$ip) {
//            $console->writeErrorStatus('IP address for ' . $this->getName() . ' environment is not set');
//            $ip = $console->ask('What ip?');
//        }

        $console->writeStatus('IP address set to \'' . $ipAddress . '\'');
        $this->getEnvironmentConfig()->setIp($ipAddress);
    }

    public function setupHostname(Startup $console)
    {
        $hostname = $this->getEnvironmentConfig()->getHostname($this->getConfig());
        while (!$hostname) {
            $console->writeErrorStatus('Application hostname is not set');
            $hostname = $console->ask('What url will your app be available at');
        }
        $console->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $this->getEnvironmentConfig()->setHostname($hostname);
    }

    protected function getHosts()
    {
        $hostsFile = StartupCommand::HOSTS . strtolower($this->getName()) . '.json';
        $hosts = new Hosts($hostsFile, $this->getName());
        return $hosts->getData()['hosts'];
    }
}
