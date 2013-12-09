<?php
namespace CG\Skeleton\Composer;

class Composer
{
    protected $path;
    protected $data;
    // TODO add console

    public function __construct($path)
    {
        $this->path = $path;
        $this->load();
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

    public function addRequire($require)
    {
        $beforeHash = hash_file('md5', 'composer.json');
        exec('php composer.phar require --no-update ' . $require);
        $afterHash = hash_file('md5', 'composer.json');

        if ($beforeHash != $afterHash) {
            $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer...\n\t* "
                . $require . Console::COLOR_GREEN);
            exec('php composer.phar update ' . explode(':', $require)[0]);
        }

        return $this;
    }

    public function updateComposer($require = null)
    {
        $this->getConsole()->writeln(Console::COLOR_GREEN . ' + ' . "Updating composer...\n\t* "
            . $require . Console::COLOR_GREEN);

        if (isset($require)) {
            exec('php composer.phar update ' . explode(':', $require)[0]);
        } else {
            exec('php composer.phar update');
        }
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}