<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$controllerComponents = array(
    'CG'
);

$libraryComponents = array(
    'CG'
);

$vendorComponents = array_merge($frameworkComponents, require 'vendor_components.php');

$componentTypes = [
    'controllers' => $controllerComponents,
    'library' => $libraryComponents,
    'vendor' => $vendorComponents
];