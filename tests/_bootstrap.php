<?php
// This is global bootstrap for autoloading
define('TEST_DIR', __DIR__);
spl_autoload_register(function ($className)
{
    $includePaths = [__DIR__ . "/api/_pages/", __DIR__ . "/../vendor/channelgrabber/codeception/CG/"];

    $className = ltrim($className, '\\');
    $filename  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $filename .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    foreach ($includePaths as $filePath) {
        if (file_exists($filePath . $filename)) {
            include_once $filePath . $filename;
            return;
        }
    }

    return false;
}, true, true);
