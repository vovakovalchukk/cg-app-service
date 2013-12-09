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

    public function updateComposer()
    {

    }

    public function setKey($key, $value)
    {
        $data =& $this->data;
        foreach (explode('|', $key) as $currentKey) {
            if (!isset($data[$currentKey])) {
                $data[$currentKey] = array();
            }
            $data =& $data[$currentKey];
        }

        if (is_array($value)) {
            $data = array_merge($data, $value);
        } else {
            $data = $value;
        }
    }

    public function removeKey($key)
    {
        $data =& $this->data;
        $keys = explode('|', $key);
        $key = array_pop($keys);

        foreach ($keys as $currentKey) {
            if (!isset($data[$currentKey])) {
                return;
            }
            $data =& $data[$currentKey];
        }

        unset($data[$key]);
    }

    public function save()
    {
        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}