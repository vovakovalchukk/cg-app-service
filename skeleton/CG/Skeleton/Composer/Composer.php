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

    public function addRequires($requires = array(), $update = true)
    {
        $requiresToUpdate = array();
        foreach ($requires as $require) {
            if ($this->addRequire($require, false)) {
                $requiresToUpdate[] = $require;
            }
        }

        if ($update) {
            $this->updateComposer($requiresToUpdate);
        }
    }

    public function addRequire($require, $update = true)
    {
        $updateRequired = false;

        $beforeHash = hash_file('md5', 'composer.json');
        exec('php composer.phar require --no-update ' . $require);
        $afterHash = hash_file('md5', 'composer.json');

        $hasComposerJsonChanged = $beforeHash != $afterHash;

        if ($update && $hasComposerJsonChanged) {
            $this->updateComposer(array($require));
            $updateRequired = false;
        } else if ($hasComposerJsonChanged) {
            $updateRequired = true;
        }

        $this->load();
        return $updateRequired;
    }

    public function updateComposer($requires = null)
    {
        $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer..." . Console::COLOR_GREEN);

        $output = '';
        $return = 0;
        if (!is_null($requires)) {
            if (empty($requires)) {
                return;
            }
            $this->getConsole()->writeln("\t* " . implode("\n\t* ",$requires));
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