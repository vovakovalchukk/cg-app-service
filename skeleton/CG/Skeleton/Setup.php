<?php
namespace CG\Skeleton;

use SplObjectStorage;
use Zend\Config\Config;

class Setup
{
    const PROJECT_BASE_PATH = 'PROJECT_BASE_PATH';

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

    public function getProjectBasePath()
    {
        $projectBasePath = $this->getConfig()->get(static::PROJECT_BASE_PATH);
        if ($projectBasePath) {
            return $projectBasePath;
        }

        if (isset($_SERVER[static::PROJECT_BASE_PATH])) {
            return $_SERVER[static::PROJECT_BASE_PATH];
        }

        return '';
    }

    public function run()
    {
        fwrite(STDOUT, 'Welcome to the Skeleton Application Setup' . PHP_EOL);
        $this->setupEnvironment();
        $this->commandList();
    }

    protected function setupEnvironment()
    {
        fwrite(STDOUT, PHP_EOL);
        $this->setupProjectBasePath();
        fwrite(STDOUT, PHP_EOL);
    }

    protected function setupProjectBasePath()
    {
        $valid = true;
        $projectBasePath = $this->getProjectBasePath();
        $defaultProjectBasePath = dirname(getcwd());

        while (!is_dir($projectBasePath)) {
            $valid = false;
            fwrite(STDOUT, ' - ' . static::PROJECT_BASE_PATH . ' is not set or is not a valid directory' . PHP_EOL);
            fwrite(STDOUT, '   Please enter new path [' . $defaultProjectBasePath . ']: ');
            $projectBasePath = trim(fgets(STDIN)) ?: $defaultProjectBasePath;
        }

        fwrite(STDOUT, ' + ' . static::PROJECT_BASE_PATH . ' set as \'' . $projectBasePath . '\'' . PHP_EOL);
        if (!$valid) {
            $this->getConfig()->offsetSet(static::PROJECT_BASE_PATH, $projectBasePath);
        }
    }

    protected function commandList()
    {
        fwrite(STDOUT, 'The following commands are available' . PHP_EOL . PHP_EOL);

        $commands = $this->getCommands();
        $commands->rewind();
        for ($i = 1; $commands->valid(); $i++, $commands->next()) {
            fwrite(STROUT, str_repeat(' ', 3) . $i . ': ' . $commands->current()->getName() . PHP_EOL);
        }
        fwrite(STDOUT, str_repeat(' ', 3) . '0: Exit' . PHP_EOL . PHP_EOL);

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