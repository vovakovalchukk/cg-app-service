<?php
namespace CG\Skeleton;

use CG\Skeleton\Module\BaseConfig;
use CG\Skeleton\Console;

trait ComposerTrait
{
    public function updateComposer(BaseConfig $moduleConfig,  $requires)
    {
        if ($moduleConfig->isEnabled()) {
            $beforeHash = hash_file('md5', 'composer.json');
            foreach($requires as $require) {
                exec('php composer.phar require --no-update ' . $require);
            }
            $afterHash = hash_file('md5', 'composer.json');

            if ($beforeHash != $afterHash) {
                $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer...\n\t* "
                                             . implode("\n\t* ",$requires) . Console::COLOR_GREEN);
                $requiresToUpdate = array();
                foreach($requires as $require) {
                    $requiresToUpdate[] = explode(':', $require)[0];
                }
                exec('php composer.phar update ' . implode(' ', $requiresToUpdate));
            }
        }
    }

    protected function doesRequireExist(String $require) {

    }

    protected function getComposerJson() {
        $composerConfigJson = file_get_contents($fileName);
        $composerConfig = json_decode($composerConfigJson);
        if(isset($composerConfig->require)) {
            $requireArray = (array)$composerConfig->require;
            ksort($requireArray);
            $composerConfig->require = (object)$requireArray;
            $composerConfigJson = json_encode($composerConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            file_put_contents($fileName, $composerConfigJson);
            exec('sed -i -e s/\'"_empty_"\'/\'""\'/ composer.json');
            exec("git add {$fileName}");
        }
    }

    abstract public function getConsole();
}