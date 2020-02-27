<?php
use CG\Stdlib\DateTime;
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
            $timeThreshold = $input->getArgument('timeThreshold');
            if (!is_null($chunkSize) && (new DateTime($timeThreshold)) > (new DateTime('now'))) {
                throw new InvalidArgumentException('timeThreshold can\'t be in the future');
            }
            $command($input, $output);
        },
        'description' => 'Cleanup any old transactions',
        'arguments' => [
            'chunkSize' => [
                'required' => false,
                'description' => 'Set the number of transactions to be cleaned up in one request',
            ],
            'timeThreshold' => [
                'required' => false,
                'description' => 'Remove transactions from before this time - this is a relative time string e.g. 1 month ago',
            ]
        ],
        'options' => []
    ],
];