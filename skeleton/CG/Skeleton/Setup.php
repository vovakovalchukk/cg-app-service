<?php
namespace CG\Skeleton;

use SplObjectStorage;

class Setup
{
    protected $arguments;
    protected $config;
    protected $startupCommands;
    protected $commands;
    protected $shutdownCommands;

    public function __construct(Arguments $arguments, Config $config)
    {
        $this->setArguments($arguments)->setConfig($config);
        $this->startupCommands = new SplObjectStorage();
        $this->commands = new SplObjectStorage();
        $this->shutdownCommands = new SplObjectStorage();
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

    public function addStartupCommand(StartupCommand $startupCommand)
    {
        $this->startupCommands->attach($startupCommand);
    }

    public function setStartupCommands($startupCommands)
    {
        $this->startupCommands = $startupCommands;
        return $this;
    }

    public function getStartupCommands()
    {
        return $this->startupCommands;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function addCommand(Command $command)
    {
        $this->commands->attach($command);
    }

    public function addShutdownCommand(ShutdownCommand $shutdownCommand)
    {
        $this->shutdownCommands->attach($shutdownCommand);
    }

    public function getShutdownCommands()
    {
        return $this->shutdownCommands;
    }

    public function run()
    {
        fwrite(STDOUT, 'Welcome to the Skeleton Application Setup' . PHP_EOL);

        $this->setupEnvironment();
        foreach ($this->getStartupCommands() as $command) {
            $command->run($this->getArguments(), $this->getConfig());
        }

        while ($this->commandList());

        foreach ($this->getShutdownCommands() as $command) {
            $command->run($this->getArguments(), $this->getConfig());
        }
    }

    protected function setupEnvironment()
    {
        fwrite(STDOUT, PHP_EOL);
        $this->setupProjectBasePath();
        $this->setupInfrastructurePath();
        $this->setupBranch();
        $this->setupNode();
        $this->setupAppName();
        $this->setupHostname();
        $this->setupVmPath();
        fwrite(STDOUT, PHP_EOL);
    }

    protected function setupProjectBasePath()
    {
        $saveToConfig = false;
        $projectBasePath = $this->getConfig()->getProjectBasePath();
        $defaultProjectBasePath = dirname(getcwd());

        while (!is_dir($projectBasePath)) {
            $saveToConfig = true;
            fwrite(STDOUT, ' - ' . Config::PROJECT_BASE_PATH . ' is not set or is not a valid directory' . PHP_EOL);
            fwrite(STDOUT, '   Please enter new path [' . $defaultProjectBasePath . ']: ');
            $projectBasePath = trim(fgets(STDIN)) ?: $defaultProjectBasePath;
        }

        fwrite(STDOUT, ' + ' . Config::PROJECT_BASE_PATH . ' set as \'' . $projectBasePath . '\'' . PHP_EOL);
        if ($saveToConfig) {
            $this->getConfig()->offsetSet(Config::PROJECT_BASE_PATH, $projectBasePath);
        }
    }

    protected function setupInfrastructurePath()
    {
        $saveToConfig = false;
        $infrastructurePath = $this->getConfig()->getInfrastructurePath();

        while (!is_dir($infrastructurePath)) {
            $saveToConfig = false;
            fwrite(STDOUT, '   Do you have a local copy of ' . Config::INFRASTRUCTURE_NAME . ' [Y,n]: ');

            $localCopy = strtolower(trim(fgets(STDIN))) ?: 'y';
            if ($localCopy == 'y') {
                $saveToConfig = true;
                fwrite(STDOUT, '   Please enter new path: ');
                $infrastructurePath = trim(fgets(STDIN));
            } else if ($localCopy == 'n') {
                passthru('git clone ' . Config::INFRASTRUCTURE_REPOSITORY . ' ' . $infrastructurePath);
            }
        }

        fwrite(STDOUT, ' + ' . Config::INFRASTRUCTURE_PATH . ' set as \'' . $infrastructurePath . '\'' . PHP_EOL);
        if ($saveToConfig) {
            $this->getConfig()->offsetSet(Config::INFRASTRUCTURE_PATH, $infrastructurePath);
        }
    }

    protected function setupBranch()
    {
        $branch = $this->getConfig()->getBranch();
        while (!$branch || !$this->validateBranch($branch)) {
            if ($branch) {
                fwrite(STDOUT, '   Do you want to create branch \'' . $branch . '\' [Y,n]: ');
                $newBranch = strtolower(trim(fgets(STDIN))) ?: 'y';

                if ($newBranch == 'y') {
                    passthru(
                        'cd ' . $this->getConfig()->getInfrastructurePath() . ';'
                            . ' git branch --no-track ' . $branch . ' ' . Config::INFRASTRUCTURE_BRANCH
                    );
                    break;
                }
            }

            fwrite(STDOUT, ' - ' . Config::BRANCH . ' is not set' . PHP_EOL);
            fwrite(STDOUT, '   Please enter branch name: ');
            $branch = trim(fgets(STDIN));
        }

        fwrite(STDOUT, ' + ' . Config::BRANCH . ' set as \'' . $branch . '\'' . PHP_EOL);
        $this->getConfig()->offsetSet(Config::BRANCH, $branch);
    }

    protected function validateBranch($branch)
    {
        exec(
            'cd ' . $this->getConfig()->getInfrastructurePath() . ';'
                . ' git fetch > /dev/null;'
                . ' git ls-remote --exit-code . ' . $branch . ' > /dev/null'
                . ' || git ls-remote --exit-code . origin/' . $branch . ' > /dev/null',
            $output,
            $exitCode
        );

        return $exitCode == 0;
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
        $this->setupConfigValue(Config::NODE, $this->getConfig()->getNode(), 'What node will your application go on');
    }

    protected function setupAppName()
    {
        $this->setupConfigValue(Config::APP_NAME, $this->getConfig()->getAppName(), 'What will your app be called');
    }

    protected function setupHostname()
    {
        $this->setupConfigValue(Config::HOST_NAME, $this->getConfig()->getHostname(), 'What url will your app be available at');
    }

    protected function setupVmPath()
    {
        $this->setupConfigValue(
            Config::VM_PATH,
            $this->getConfig()->getVmPath(),
            'What is the absolute path of your app on the vm',
            '/var/www/' . $this->getConfig()->getAppName()
        );
    }

    protected function commandList()
    {
        fwrite(STDOUT, 'The following commands are available' . PHP_EOL . PHP_EOL);

        $commands = $this->getCommands();
        $commands->rewind();

        for ($i = 1; $commands->valid(); $i++, $commands->next()) {
            fwrite(
                STDOUT,
                str_repeat(' ', 3) . str_pad($i, strlen($commands->count()), ' ', STR_PAD_LEFT)
                    . ': ' . $commands->current()->getName() . PHP_EOL
            );
        }

        fwrite(
            STDOUT,
            str_repeat(' ', 3) . str_pad('0', strlen($commands->count()), ' ', STR_PAD_LEFT)
                . ': Exit' . PHP_EOL . PHP_EOL
        );

        $command = null;
        while (!is_numeric($command) || $command < 0 || $command > $commands->count()) {
            fwrite(STDOUT, 'Run Command: ');
            $command = trim(fgets(STDIN));
        }

        if ($command == 0) {
            return;
        }

        $commands->rewind();
        for ($i = 1; $i < $command; $i++) {
            $commands->next();
        }

        return $commands->current()->run($this->getArguments(), $this->getConfig());
    }
}