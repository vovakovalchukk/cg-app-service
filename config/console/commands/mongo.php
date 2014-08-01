<?php
use CG\Mongo\Migration\ItemPurchaseDateStatusMigration;

return array(
    'mongo:itemPurchaseDateStatusMigration' => array(
        'command' => function () use ($di) {
                $command = $di->get(ItemPurchaseDateStatusMigration::class);
                $command->migrate();
            },
        'description' => 'Migrate OrderItem data which is in mongo to add missing purchaseDate + status',
        'arguments' => array(
        ),
        'options' => array(

        )
    )
);
