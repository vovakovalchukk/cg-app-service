<?php
namespace CG\Skeleton\Composer;

use CG\Skeleton\Console;
use CG\Skeleton\Module\BaseConfig;

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

    public function addRequires(BaseConfig $moduleConfig, $requires = array(), $update = true)
    {
        $requiresToUpdate = array();
        foreach ($requires as $require) {
            if ($this->addRequire($moduleConfig, $require, false)) {
                $requiresToUpdate[] = $require;
            }
        }

        if ($update) {
            $this->updateComposer($requiresToUpdate);
        }
    }

    public function addRequire(BaseConfig $moduleConfig, $require, $update = true)
    {
        $updateRequired = false;

        $beforeHash = hash_file('md5', 'composer.json'); // TODO remove hash check - check against loaded data?
        exec('php composer.phar require --no-update ' . $require);
        $afterHash = hash_file('md5', 'composer.json');

        $hasComposerJsonChanged = ($beforeHash != $afterHash);

        if ($update && $hasComposerJsonChanged) {
            echo "updating single require\n";
            $this->updateComposer(array($require));
            $updateRequired = false;
        } else if ($hasComposerJsonChanged) {
            echo "composer has changed\n";
            $updateRequired = true;
        }

        $this->load();
        $requireData = explode(':', $require);
        $moduleConfig->setComposerRequire($requireData[0], $requireData[1]);
        return $updateRequired;

//        if (!$this->requireExists($require)) {
//            // No entry exists. Add to composer.json
//        } else if ($this->getRequireVersion($require) != explode(':', $require)[1]) {
//            // if config.version == getRequireVersion()
//            //    skeleton added last entry. update it :-)
//        } else {
//            // do nothing. version is the same.
//        }
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
            print_r($requires);
            $this->getConsole()->writeln("\t* " . implode("\n\t* ",$requires));
            $packageNames = array();
            foreach ($requires as $require) {
                $packageNames[] = $this->getPackageName($require);
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

    public function removeRequire($require, $update = true)
    {
        // TODO extract require array get
        $composerConfig =& $this->data;
        if(!isset($composerConfig['require'])) {
            return;
        }
        $requireArray =& $composerConfig['require'];

        var_dump($requireArray);

        echo $this->requireExists($require) ? "Require exists\n" : "require DOESN'T exist\n";
        if($this->requireExists($require)) {
            unset($requireArray[$this->getPackageName($require)]);
        }

        var_dump($this->data);

        if ($update) {
            $this->updateComposer(array($require));
        }

        $this->save();
        return $this;
    }

    public function requireExists($require) {
        // TODO extract require array get
        $composerConfig =& $this->data;
        if(!isset($composerConfig['require'])) {
            return;
        }
        $requireArray =& $composerConfig['require'];

        $packageName = $this->getPackageName($require);
        echo "package: $packageName\n";
        foreach ($requireArray as $name => $version) {
            if ($name == $packageName) {
                return true;
            }
        }
        return false;
    }

    protected function getPackageName($require)
    {
        return explode(':', $require)[0];
    }

    protected function getRequireVersion($require)
    {
        // TODO extract require array get
        $composerConfig =& $this->data;
        if(!isset($composerConfig['require'])) {
            return;
        }
        $requireArray = $composerConfig['require'];

        return $requireArray[explode(':', $require)[0]];
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}