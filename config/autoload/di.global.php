<?php
$dataDiPath = dirname(dirname(__DIR__)) . '/data/di';

$definitions = array();
foreach (glob($dataDiPath . "/*-definition.php") as $filepath) {
    $definitions[] = $filepath;
}

return array(
    'di' => array(
        'definition' => array(
            'runtime' => array(
                'enabled' => true
            ),
            'compiler' => $definitions
        )
    )
);