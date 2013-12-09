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
                var_dump(implode(' ', $requiresToUpdate));
                exec('php composer.phar update ' . implode(' ', $requiresToUpdate));
            }
        }
    }

    abstract public function getConsole();
}