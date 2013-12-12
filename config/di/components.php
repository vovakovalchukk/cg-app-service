<?php
$frameworkComponents = require 'framework_components.php';
$phpInternalComponents = require 'php_internal_components.php';

$controllerComponents = array(
    'CG'
);

$libraryComponents = array(
    'CG'
);

$vendorComponents = array_merge($frameworkComponents, array(
    'channelgrabber_http_CG_Http',
    'channelgrabber_slim_CG_Slim',
    'channelgrabber_stdlib_CG_Stdlib',
    'channelgrabber_validation_CG_Validation',
    'channelgrabber_vnderror_CG_VndError',
    'guzzle_guzzle_src_Guzzle',
    'nocarrier_hal_src_Nocarrier',
//    'robmorgan_phinx_src_Phinx',
    'slim_slim_Slim',
    'zendframework_zend-config_Zend_Config',
    'zendframework_zend-db_Zend_Db',
    'zendframework_zend-di_Zend_Di',
    'zendframework_zend-inputfilter_Zend_InputFilter',
    'zendframework_zend-servicemanager_Zend_ServiceManager',
//    'zendframework_zend-validator_Zend_Validator',
));

$componentTypes = [
    'controllers' => $controllerComponents,
    'library' => $libraryComponents,
    'vendor' => $vendorComponents
];
$components = ['di'];