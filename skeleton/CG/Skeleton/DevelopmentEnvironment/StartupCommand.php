<?php
namespace CG\Skeleton\DevelopmentEnvironment;

use CG\Skeleton\StartupCommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config as SkeletonConfig;
use CG\Skeleton\DevelopmentEnvironment\CommandTrait;
use Zend\Config\Config as ZendConfig;
use CG\Skeleton\DevelopmentEnvironment\EnvironmentFactory;
use CG\Skeleton\Console\Startup;

class StartupCommand implements StartupCommandInterface
{
    use CommandTrait;
    use \CG\Skeleton\GitTicketIdTrait;

    const HOSTS = 'data_bags/hosts/';

    protected $defaults;
    protected $console;

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

    public function runCommands(Arguments $arguments, SkeletonConfig $config)
    {
        $this->setupIp($config);
    }

    protected function setupHostname(Config $config, Config $currentEnvironmentConfig)
    {
        $hostname = $currentEnvironmentConfig->getHostname();
        while (!$hostname) {
            $this->getConsole()->writeErrorStatus('Application hostname is not set');
            $hostname = $this->getConsole()->ask('What url will your app be available at');
        }
        $this->getConsole()->writeStatus('Application hostname set to \'' . $hostname . '\'');
        $currentEnvironmentConfig->setHostname($hostname);
    }

    protected function setupIp(Config $config)
    {
        EnvironmentFactory::build($this->getConsole(), $config->getEnvironment(), $config)->setupIp();
    }

    // Load Hosts object with current file data. Add new host. Save Hosts.
    protected function saveHost(Config $config)
    {
        $host = $config->getHost();

        $hostsFile = static::HOSTS . 'local' . '.json';
        $hosts = new Hosts($hostsFile);

        // Save to config
        $hosts->setHost('host', $config->getHostname(), '127.0.0.1');

        $hosts->save();

        exec(
            'git add ' . $hostsFile . ';'
            . ' git commit -m "' . $this->getGitTicketId() . ' (SKELETON) Updated role ' . $host . '" --only -- ' . $hostsFile
        );
    }

    public function getHosts()
    {
        return $this->hosts;
    }
}