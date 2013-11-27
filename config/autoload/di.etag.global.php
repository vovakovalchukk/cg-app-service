<?php
use Slim\Http\Headers as SlimHeaders;
use CG\Slim\Stdlib\Http\Headers as CgHeaders;

return array(
    'di' => array(
        'instance' => array(
            'aliases' => array(
                'SlimRequestHeaders' => SlimHeaders::class,
                'SlimResponseHeaders' => SlimHeaders::class,
                
                'RequestHeaders' => CgHeaders::class,
                'ResponseHeaders' => CgHeaders::class
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