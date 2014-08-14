<?php

use CG\Order\Service\Item\Transaction\RetryUpdateItemAndStock;

return [
    'retry:itemFailedSaves' => [
        'command' => function () use ($di) {
                $command = $di->get(RetryUpdateItemAndStock::class);
                $command();
            },
        'description' => 'Retry failed transactions of order item saves w/ stock adjustments',
        'arguments' => [
        ],
        'options' => [

        ]
    ]
];
