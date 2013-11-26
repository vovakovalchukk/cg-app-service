<?php

return array(
    'di' => array(
        'instance' => array(
            'aliases' => array(
                'SlimRequestHeaders' => 'Slim\Http\Headers',
                'SlimResponseHeaders' => 'Slim\Http\Headers',
            ),
            'RequestHeaders' => array(
                'parameters' => array(
                    'slimHeaders' => 'SlimRequestHeaders'
                )
            ),
            'ResponseHeaders' => array(
                'parameters' => array(
                    'slimHeaders' => 'SlimResponseHeaders'
                )
            )
        )
    )
);

