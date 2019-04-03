<?php
use CG\Walmart\Command\GetItemsReportForAccounts;
use Zend\Di\Di;

/** @var Di $di */
return [
    'walmart:getItemsReportForAccounts' => [
        'command' => function () use ($di) {
            /** @var GetItemsReportForAccounts $command */
            $command = $di->get(GetItemsReportForAccounts::class);
            $command();
        },
        'description' => 'Gets the item reports for the currently enabled walmart accounts',
        'arguments' => [
        ],
        'options' => [
        ],
    ],
];
