#!/usr/bin/env php

<?php

$composerLockFilePath = dirname(__DIR__) . '/composer.lock';
$composerLockFile = json_decode(file_get_contents($composerLockFilePath), true);

foreach($composerLockFile['packages'] as &$package) {
    $package['dist']['shasum'] = null;
}

foreach($composerLockFile['packages-dev'] as &$package) {
    $package['dist']['shasum'] = null;
}

file_put_contents($composerLockFilePath, json_encode($composerLockFile, JSON_PRETTY_PRINT));