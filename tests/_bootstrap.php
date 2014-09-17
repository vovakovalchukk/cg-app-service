<?php
// This is global bootstrap for autoloading
define('TEST_DIR', __DIR__);
spl_autoload_register(function ($className)
{
    if(strpos($className, "Codeception") !== false) {
        $prefix = 'Codeception';
        $baseDir = __DIR__."/../vendor/channelgrabber/codeception/src/";
        $len = strlen($prefix);
        $relativeClass = substr($className, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            include_once $file;
            return;
        }
    }

    $testPagesPath = __DIR__ . "/api/_pages/";
    $className = ltrim($className, '\\');
    $filename  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $filename .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    if (file_exists($testPagesPath . $filename)) {
        include_once $testPagesPath . $filename;
        return;
    }

    return false;
}, true, true);

