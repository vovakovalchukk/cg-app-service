<?php
use CG\Order\Service\Notification\Generator;
use CG\Order\Service\Notification\GeneratorInterface;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                GeneratorInterface::class => Generator::class,
            ],
        ],
    ],
];