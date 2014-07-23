<?php
use CG\Slim\Etag\Config;

return array(
    'di' => array(
        'definition' => [
            'class' => [
                Config::class => [
                    'methods' => [
                        '__construct' => [
                            'entityClass' => [
                                'type' => false
                            ],
                            'mapperClass' => [
                                'type' => false
                            ],
                            'serviceClass' => [
                                'type' => false
                            ]
                        ],
                        'setEntityClass' => [
                            'entityClass' => [
                                'type' => false
                            ]
                        ],
                        'setMapperClass' => [
                            'mapperClass' => [
                                'type' => false
                            ]
                        ],
                        'setServiceClass' => [
                            'serviceClass' => [
                                'type' => false
                            ]
                        ],
                    ]
                ]
            ]
        ]
    )
);