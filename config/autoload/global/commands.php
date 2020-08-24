<?php

use CG\Order\Command\ClearCachedCustomerCountsByPattern as ClearCachedCustomerCountsByPatternCommand;

return [
    'di' => [
        'instance' => [
            ClearCachedCustomerCountsByPatternCommand::class => [
                'parameter' => [
                    'predisClient' => 'reliable_redis',
                ],
            ],
        ],
        'preferences' => [

        ],
    ],
];