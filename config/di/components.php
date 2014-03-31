<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$moduleComponents = [];

$vendorComponents = array_merge($frameworkComponents, require 'vendor_components.php');

$componentTypes = [
    'module' => $moduleComponents,
    'vendor' => $vendorComponents
];