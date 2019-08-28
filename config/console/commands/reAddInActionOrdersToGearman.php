<?php

use CG\Order\Command\ReAddInActionOrdersToGearman;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'reAddInActionOrdersToGearman' => [
        'description' => 'Add in progress Orders to gearman queues',
        'arguments' => [],
        'options' => [],
        'modulus' => true,
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $command = $di->get(ReAddInActionOrdersToGearman::class);
            $command();
        }
    ]
];
