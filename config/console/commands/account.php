<?php
use CG\Account\Command\Sales\Disable as SalesAccountDisable;
use Zend\Di\Di;

/** @var Di $di */
return [
    'account:disableOldSalesAccounts' => [
        'command' => function () use ($di) {
            /** @var ListingImport $command */
            $command = $di->get(SalesAccountDisable::class);
            $command->disableUnusedAccounts();
        },
        'description' => 'Disable all sales accounts for anyone who hasnt used the system in a while',
        'arguments' => [
        ],
        'options' => [
        ],
    ],
];
