<?php
namespace CG\Skeleton;

use SplObjectStorage;
use Zend\Config\Config;

class Setup
{
    protected $commands;
    protected $arguments;
    protected $config;

    public function __construct(SplObjectStorage $commands, Arguments $arguments, Config $config)
    {
        $this->setCommands($commands)->setArguments($arguments)->setConfig($config);
    }

    public function setCommands(SplObjectStorage $commands)
    {
        $this->commands = $commands;
        return $this;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function setArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function run()
    {
        fwrite(STDOUT, 'Welcome to the Skeleton Application Setup' . PHP_EOL);
        $this->commandList();
    }

    protected function commandList()
    {
        fwrite(STDOUT, 'The following commands are available' . PHP_EOL . PHP_EOL);

        fwrite(STDOUT, str_repeat(' ', 3) . '0: Exit' . PHP_EOL);

        $commands = $this->getCommands();
        $commands->rewind();
        for ($i = 1; $commands->valid(); $i++, $commands->next()) {
            fwrite(STROUT, str_repeat(' ', 3) . $i . ': ' . $commands->current()->getName() . PHP_EOL);
        }

        fwrite(STDOUT, PHP_EOL);

        $command = null;
        while (!is_numeric($command) || $command < 0 || $command > $commands->count()) {
            fwrite(STDOUT, 'Run Command: ');
            $command = trim(fgets(STDIN));
        }

        if ($command == 0) {
            return;
        }

        $commands->rewind();
        for ($i = 0; $i < $command; $i++) {
            $commands->next();
        }

        $commands->current()->run($this->getArguments(), $this->getConfig());
    }
}