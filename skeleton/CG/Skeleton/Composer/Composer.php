<?php
namespace CG\Skeleton\Composer;

use CG\Skeleton\Console;

class Composer
{
    protected $path;
    protected $data;
    protected $console;

    public function __construct(Console $console, $path)
    {
        $this->path = $path;
        $this->console = $console;
        $this->load();
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

    protected function load()
    {
        if (!is_file($this->path)) {
            return;
        }

        $jsonData = json_decode(file_get_contents($this->path), true);
        if (!is_array($jsonData)) {
            return;
        }

        $this->data = $jsonData;
    }

    public function addRequires($requires = array())
    {
        foreach ($requires as $require) {
            $this->addRequire($require);
        }
    }

    public function addRequire($require, $update = false)
    {
        $updateRequired = false;
        if ($update) {
            $beforeHash = hash_file('md5', 'composer.json');
            passthru('php composer.phar require --no-update ' . $require);
            $afterHash = hash_file('md5', 'composer.json');

            if ($beforeHash != $afterHash) {
                $updateRequired = true;
                $this->updateComposer(array($require));
            }
        } else {
            exec('php composer.phar require --no-update ' . $require);
        }

        $this->load();
        return $updateRequired;
    }

    public function updateComposer($requires = array())
    {
        $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer..." . Console::COLOR_GREEN);

        $output = '';
        $return = 0;
        if (isset($require)) {
            $this->getConsole()->writeln(implode("\n\t* ",$requires));
            $packageNames = array();
            foreach ($requires as $require) {
                $packageNames[] = explode(':', $require)[0];
            }
            exec('php composer.phar update ' . implode(' ', $packageNames), $output, $return);
        } else {
            exec('php composer.phar update', $output, $return);
        }

        if ($return != 0) {
            foreach($output as $line) {
                echo $line . "\n";
            }
        }
        return $this;
    }

    public function removeRequire($require)
    {
        $composerConfig = $this->data;
        if(!isset($composerConfig->require)) {
            return;
        }

        $requireArray = $composerConfig->require;

        print_r($requireArray);

        return $this;
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}