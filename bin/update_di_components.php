<?php
require_once 'bootstrap.php';

$exclusions = ['bin', 'composer'];
$vendorDir = 'vendor/';
$it = new DirectoryIterator($vendorDir);
foreach ($it as $file) {
    if (!$file->isDir() || in_array($file->getFilename(), $exclusions)) {
        continue;
    }

    
}