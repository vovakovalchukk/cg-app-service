<?php
use CG\Transaction\Command\Cleanup;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'transaction:cleanup' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /** @var Cleanup $command */
            $command = $di->newInstance(Cleanup::class);
            $command($output);
        },
        'description' => 'Cleanup any old transactions ',
        'arguments' => [],
        'options' => []
    ],
];