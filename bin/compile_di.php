<?php
require_once 'bootstrap.php';
require_once 'config/di/components.php';

$diDataDir = 'data/di/';

/*
$it = new DirectoryIterator($diDataDir);
foreach ($it as $file) {
    if (preg_match('/-definition.php$/', $file->getBasename(), $matches)) {
        echo $file->getBasename() . "\n";
        //unlink($file->getPathname());
    }
}

exit;
*/


$it = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($diDataDir),
    \RecursiveIteratorIterator::CHILD_FIRST
);
foreach ($it as $file) {
    if (in_array($file->getBasename(), array('.', '..')) || $file->isLink()) {
        continue;

    } elseif ($file->isDir() && !(new \FilesystemIterator($file->getPathname()))->valid()) {
        echo $file->getBasename() + "\n";
        rmdir($file->getPathname());

    } elseif ($file->isFile() && $file->getExtension() == 'php') { // chekc for symlink somewhere
        echo $file->getBasename() + "\n";
        unlink($file->getPathname());
    }
}


foreach ($libraryComponents as $component) {
    $diCompiler = new Zend\Di\Definition\CompilerDefinition;
    $dir = dirname(__DIR__) . '/library/' . str_replace('_', '/', $component);
    echo "Compiling ".$dir."\n";
    $diCompiler->addDirectory($dir);
    $diCompiler->setAllowReflectionExceptions();
    $diCompiler->compile();
    file_put_contents(
        $diDataDir . $component . '-definition.php',
        '<?php return ' . var_export($diCompiler->toArrayDefinition()->toArray(), true) . ';'
    );
}

foreach ($vendorComponents as $component) {
    $diCompiler = new Zend\Di\Definition\CompilerDefinition;
    $dir = dirname(__DIR__) . '/vendor/' . str_replace('_', '/', $component);
    echo "Compiling ".$dir."\n";
    $diCompiler->addDirectory($dir);
    $diCompiler->setAllowReflectionExceptions(true);
    $diCompiler->compile();
    file_put_contents(
        $diDataDir . $component . '-definition.php',
        '<?php return ' . var_export($diCompiler->toArrayDefinition()->toArray(), true) . ';'
    );
}