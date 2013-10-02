<?php
namespace CG\Skeleton;

use SplObjectStorage;

class Setup
{
    protected $console;
    protected $arguments;
    protected $config;
    protected $startupCommands;
    protected $commands;
    protected $shutdownCommands;

    public function __construct(Console $console, Arguments $arguments, Config $config)
    {
        $this->setConsole($console)->setArguments($arguments)->setConfig($config);
        $this->startupCommands = new SplObjectStorage();
        $this->commands = new SplObjectStorage();
        $this->shutdownCommands = new SplObjectStorage();
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

    public function addStartupCommand(StartupCommandInterface $startupCommand)
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

    public function addCommand(CommandInterface $command)
    {
        $this->commands->attach($command);
    }

    public function addShutdownCommand(ShutdownCommandInterface $shutdownCommand)
    {
        $this->shutdownCommands->attach($shutdownCommand);
    }

    public function getShutdownCommands()
    {
        return $this->shutdownCommands;
    }

    public function run()
    {
        $this->getConsole()->writeln(Console::COLOR_BLUE . 'Welcome to the Skeleton Application Setup' . Console::COLOR_RESET);

        $startupCommands = $this->getStartupCommands();
        if ($startupCommands->count() > 0) {
            $this->getConsole()->writeln();
            foreach ($startupCommands as $command) {
                $command->run($this->getArguments(), $this->getConfig());
            }
            $this->getConsole()->writeln();
        }

        while ($this->commandList());

        $shutdownCommands = $this->getShutdownCommands();
        if ($shutdownCommands->count() > 0) {
            $this->getConsole()->writeln();
            foreach ($shutdownCommands as $command) {
                $command->run($this->getArguments(), $this->getConfig());
            }
            $this->getConsole()->writeln();
        }
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
            fwrite(STDOUT, '?> ');
            $command = trim(fgets(STDIN));
        }

        if ($command == 0) {
            return false;
        }

        $commands->rewind();
        for ($i = 1; $i < $command; $i++) {
            $commands->next();
        }

        $commands->current()->run($this->getArguments(), $this->getConfig());
        return true;
    }
}