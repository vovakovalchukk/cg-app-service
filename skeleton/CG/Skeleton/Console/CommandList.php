<?php
namespace CG\Skeleton\Console;

use CG\Skeleton\Console;
use SplObjectStorage;
use CG\Skeleton\Arguments;
use CG\Skeleton\Config;

class CommandList
{
    protected $console;
    protected $commands;

    public function __construct(Console $console, SplObjectStorage $commands)
    {
        $this->setConsole($console)->setCommands($commands);
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

    public function setCommands(SplObjectStorage $commands)
    {
        $this->commands = $commands;
        return $this;
    }

    public function getCommands()
    {
        return $this->commands;
    }

    public function askAndRun(Arguments $arguments, Config $config)
    {
        $commands = $this->getCommands();

        if ($commands->count() == 0) {
            return;
        }

        $this->getConsole()->writeln('The following commands are available:');
        $indent = 3 + strlen($commands->count());

        $i = 1;
        $commands->rewind();
        foreach ($commands as $command) {
            $this->displayCommand($indent, $i++, $command->getName());
        }
        $this->displayCommand($indent, 0, 'Cancel');

        $selected = null;
        while (!is_numeric($selected) || $selected < 0 || $selected > $commands->count()) {
            $this->getConsole()->write('?> ');
            $selected = $this->getConsole()->readln();
        }

        if ($selected == 0) {
            return;
        }

        $i = 1;
        $commands->rewind();
        foreach ($commands as $command) {
            if ($selected != $i++) {
                continue;
            }
            $command->run($arguments, $config);
        }

        return (boolean) $selected;
    }

    protected function displayCommand($indent, $index, $command)
    {
        $this->getConsole()->writeln(
            Console::COLOR_GREEN . str_pad($index, $indent, ' ', STR_PAD_LEFT) . ': '
            . Console::COLOR_RESET . $command
        );
    }
}