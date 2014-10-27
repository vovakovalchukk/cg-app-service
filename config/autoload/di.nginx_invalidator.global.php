<?php

use CG\Slim\Nginx\Cache\Invalidator;

return [
    'di' => [
        'instance' => [
            Invalidator::class => [
                'shared' => true
            ]
        ]
    ]
];
