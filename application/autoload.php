<?php
set_include_path(__DIR__ . "/../controllers/" . PATH_SEPARATOR . get_include_path());
spl_autoload_register(function ($className)
{
    $className = ltrim($className, '\\');
    $filename  = '';
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $filename  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $filename .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
    $filename = stream_resolve_include_path($filename);
    if ($filename === false) {
        return false;
    }
    include_once $filename;
}, true, true);
