<?php
namespace CG\Skeleton\DevelopmentEnvironment\Command;

use CG\Skeleton\CommandInterface;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;
use CG\Skeleton\Console;
use CG\Skeleton\DevelopmentEnvironment\Environment;

class ChangeEnvironment implements CommandInterface
{
    protected $console;

    public function __construct(Console $console)
    {
        $this->setConsole($console);
    }

    public function setConsole(Console $console)
    {
        $this->console = $console;
        return $this;
    }

    public function getConsole()
    {
        return $this->console;
    }

    public function getName()
    {
        return 'Switch development environment';
    }

    public function run(Arguments $arguments, Config $config, Environment $environment)
    {
        //$this->getConsole()->askWithOptions('Please choose a development environment', array('Local', 'Dual'), 'Local');

        $availableEnvironments = array('Local', 'Dual'); // TODO temp

        $this->getConsole()->writeln('Available Development Environments:');
        foreach ($availableEnvironments as $environmentName) {
            $this->getConsole()->writeln('   * ' . $environmentName);
        }

        $chosenEnvironment = $this->getConsole()->ask('Please specify a development environment');

        $config->setEnvironment($chosenEnvironment);
    }
}