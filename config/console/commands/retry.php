<?php

return array(
    'retry:itemFailedSaves' => array(
        'command' => function () use ($di) {
//                $command = $di->get(Something::class);
//                $command();
            },
        'description' => 'Migrate OrderItem data which is in mongo to add missing purchaseDate + status',
        'arguments' => array(
        ),
        'options' => array(

        )
    )
);
