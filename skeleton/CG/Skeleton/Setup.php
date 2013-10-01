<?php
namespace CG\Skeleton;

use SplObjectStorage;
use Zend\Config\Config;

class Setup
{
    const PROJECT_BASE_PATH = 'PROJECT_BASE_PATH';
    const NODE = 'NODE';
    const APP_NAME = 'APP_NAME';

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

    public function getNode()
    {
        return $this->getConfig()->get(static::NODE);
    }

    public function getAppName()
    {
        return $this->getConfig()->get(static::APP_NAME);
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
        $this->setupNode();
        $this->setupAppName();
        fwrite(STDOUT, PHP_EOL);
    }

    protected function setupProjectBasePath()
    {
        $saveToConfig = false;
        $projectBasePath = $this->getProjectBasePath();
        $defaultProjectBasePath = dirname(getcwd());

        while (!is_dir($projectBasePath)) {
            $saveToConfig = true;
            fwrite(STDOUT, ' - ' . static::PROJECT_BASE_PATH . ' is not set or is not a valid directory' . PHP_EOL);
            fwrite(STDOUT, '   Please enter new path [' . $defaultProjectBasePath . ']: ');
            $projectBasePath = trim(fgets(STDIN)) ?: $defaultProjectBasePath;
        }

        fwrite(STDOUT, ' + ' . static::PROJECT_BASE_PATH . ' set as \'' . $projectBasePath . '\'' . PHP_EOL);
        if ($saveToConfig) {
            $this->getConfig()->offsetSet(static::PROJECT_BASE_PATH, $projectBasePath);
        }
    }

    protected function setupNode()
    {
        $node = $this->getNode();
        while (!$node) {
            fwrite(STDOUT, ' - ' . static::NODE . ' is not set' . PHP_EOL);
            fwrite(STDOUT, '   What node will your application go on: ');
            $node = trim(fgets(STDIN));
        }

        fwrite(STDOUT, ' + ' . static::NODE . ' set as \'' . $node . '\'' . PHP_EOL);
        $this->getConfig()->offsetSet(static::NODE, $node);
    }

    protected function setupAppName()
    {
        $appName = $this->getAppName();
        while (!$appName) {
            fwrite(STDOUT, ' - ' . static::APP_NAME . ' is not set' . PHP_EOL);
            fwrite(STDOUT, '   What will your app be called: ');
            $appName = trim(fgets(STDIN));
        }

        fwrite(STDOUT, ' + ' . static::APP_NAME . ' set as \'' . $appName . '\'' . PHP_EOL);
        $this->getConfig()->offsetSet(static::APP_NAME, $appName);
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