<?php
use CG\Slim\Etag\Config\Entity;

return array(
    'di' => array(
        'definition' => [
            'class' => [
                Entity::class => [
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