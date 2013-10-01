<?php
namespace CG\Skeleton;

use SplObjectStorage;
use Zend\Config\Config;

class Setup
{
    const PROJECT_BASE_PATH = 'PROJECT_BASE_PATH';
    const NODE = 'NODE';
    const APP_NAME = 'APP_NAME';
    const HOST_NAME = 'HOST_NAME';
    const DOMAIN = 'channelgrabber.com';
    const VM_PATH = 'VM_PATH';

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

    public function getHostname()
    {
        return $this->getConfig()->get(static::HOST_NAME, $this->getAppName() . '.' . static::DOMAIN);
    }

    public function getVmPath()
    {
        return $this->getConfig()->get(static::VM_PATH);
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
        $this->setupHostname();
        $this->setupVmPath();
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

    protected function setupConfigValue($key, $value, $question, $default = null)
    {
        while (!$value) {
            fwrite(STDOUT, ' - ' . $key . ' is not set' . PHP_EOL);
            fwrite(STDOUT, '   ' . $question . ($default ? ' [' . $default . ']' : '') . ': ');
            $value = trim(fgets(STDIN)) ?: $default;
        }

        fwrite(STDOUT, ' + ' . $key . ' set as \'' . $value . '\'' . PHP_EOL);
        $this->getConfig()->offsetSet($key, $value);
    }

    protected function setupNode()
    {
        $this->setupConfigValue(static::NODE, $this->getNode(), 'What node will your application go on');
    }

    protected function setupAppName()
    {
        $this->setupConfigValue(static::APP_NAME, $this->getAppName(), 'What will your app be called');
    }

    protected function setupHostname()
    {
        $this->setupConfigValue(static::HOST_NAME, $this->getHostname(), 'What url will your app be available at');
    }

    protected function setupVmPath()
    {
        $this->setupConfigValue(
            static::VM_PATH,
            $this->getVmPath(),
            'What is the absolute path of your app on the vm',
            '/var/www/' . $this->getAppName()
        );
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