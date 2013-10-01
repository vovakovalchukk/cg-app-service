<?php
return array(
    'di' => array(
        'instance' => array(
            'Slim\View' => array(
                'parameters' => array(
                    'Slim\View::setTemplatesDirectory:directory' => dirname(dirname(__DIR__)) . '/view'
                )
            )
        )
    )
);