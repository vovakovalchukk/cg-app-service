<?php
namespace CG\Skeleton\Composer;

use CG\Skeleton\Console;
use CG\Skeleton\Module\BaseConfig;

class Composer
{
    protected $path;
    protected $data;
    protected $requireData;
    protected $console;

    public function __construct(Console $console, $path)
    {
        $this->path = $path;
        $this->console = $console;
        $this->load();
        $this->requireData =& $this->data['require'];
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
        $requireExplode = explode(':', $require);

        $newVersion = $this->requireExists($require) ?
            $this->getRequireVersion($require) != $requireExplode[1] : false;

        $skeletonCommittedLastRequire = $this->requireExists($require) ?
            $this->getRequireVersion($require) == $moduleConfig->getComposerRequireVersion($requireExplode[0]) : false;

        if (!$this->requireExists($require) || ($newVersion && $skeletonCommittedLastRequire)) {
            $beforeHash = hash_file('md5', 'composer.json');
            exec('php composer.phar require --no-update ' . $require);
            $afterHash = hash_file('md5', 'composer.json');
            $hasComposerJsonChanged = ($beforeHash != $afterHash);

            if ($update && $hasComposerJsonChanged) {
                $this->updateComposer(array($require));
                $updateRequired = false;
            } else if ($hasComposerJsonChanged) {
                $updateRequired = true;
            }

            $this->load();
            return $updateRequired;
        }

        $moduleConfig->setComposerRequire($requireExplode[0], $requireExplode[1]);
        return false;
    }

    public function updateComposer($requires = null)
    {
        $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer..." . Console::COLOR_GREEN);

        $output = array();
        $return = 0;
        if (!is_null($requires)) {
            if (empty($requires)) {
                return;
            }
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

    public function removeRequires(BaseConfig $moduleConfig, array $requires, $update = true)
    {
        foreach ($requires as $require) {
            $this->removeRequire($moduleConfig, $require, false);
        }

        if ($update) {
            $this->updateComposer($requires);
        }
    }

    public function removeRequire(BaseConfig $moduleConfig, $require, $update = true)
    {
        $requireExplode = explode(':', $require);
        $requireData =& $this->requireData;

        if($this->requireExists($require)) {
            unset($requireData[$requireExplode[0]]);
        }

        if ($update) {
            $this->updateComposer(array($require));
        }

        $this->save();
        $moduleConfig->removeComposerRequire($requireExplode[0]);
        return $this;
    }
    // TODO remove getpackagename ()

    public function requireExists($require)
    {
        $requireData = $this->requireData;

        $packageName = $this->getPackageName($require);
        foreach ($requireData as $name => $version) {
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
        return $this->requireData[$this->getPackageName($require)];
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}