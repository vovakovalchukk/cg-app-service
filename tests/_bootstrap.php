<?php
// This is global bootstrap for autoloading 
function getTestPagesPath()
{
    return __DIR__ . "/api/_pages/";
}

function getCustomModulesPath()
{
    return __DIR__ . "/../vendor/channelgrabber/codeception/CG/";
}

set_include_path(getTestPagesPath() . PATH_SEPARATOR . getCustomModulesPath() . PATH_SEPARATOR . get_include_path());

spl_autoload_register(function ($className)
{
    $testPagesPath = getTestPagesPath();
    $customModulesPath = getCustomModulesPath();

    $className = ltrim($className, '\\');
    $filename  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }

    $filename .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

    $filePath = false;
    if (file_exists($testPagesPath . $filename)) {
        $filePath = $testPagesPath;
    } else if (file_exists($customModulesPath . $filename)) {
        $filePath = $customModulesPath;
    }

    if ($filePath == false) {
        return false;
    }
    include_once $filename;
}, true, true);
