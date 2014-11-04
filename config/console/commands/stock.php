<?php

use Symfony\Component\Console\Input\InputInterface;
use CG\Stock\Command\ProcessAudit;

return [
    'stock:processAudit' => [
        'command' => function (InputInterface $input) use ($di) {
            $setSize = $input->getArgument('setSize');
            $command = $di->get(ProcessAudit::class);
            $command($setSize);
        },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get orders from the respective channel.',
        'arguments' => [
            'setSize' => [
                'required' => false,
                'default' => 100
            ]
        ],
        'options' => [

        ]
    ]
];
