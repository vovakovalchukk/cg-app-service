<?php
use CG\Transaction\Command\Cleanup;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'transaction:cleanup' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /** @var Cleanup $command */
            $command = $di->newInstance(Cleanup::class);
            $chunkSize = $input->getArgument('chunkSize');
            if (!preg_match('/^[1-9][0-9]*$/', $chunkSize)) {
                throw new InvalidArgumentException('Argument "chunkSize" should be an positive integer');
            }
            $command($output, $chunkSize);
        },
        'description' => 'Cleanup any old transactions',
        'arguments' => [
            'chunkSize' => [
                'required' => false,
                'description' => 'Set the number of transactions to be cleaned up in one request',
            ],
        ],
        'options' => []
    ],
];