<?php

use CG\Stripe\Command\SendUsage;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'stripe:sendUsage' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $from = $input->getArgument('from') ? new \DateTime($input->getArgument('from')) : null;
            $to = $input->getArgument('to') ? new \DateTime($input->getArgument('to')) : null;
            $organisationUnitId = $input->hasArgument('organisationUnitId') ? $input->getArgument('organisationUnitId') : null;
            /** @var SendUsage $command */
            $command = $di->newInstance(SendUsage::class);
            $command($from, $to, $organisationUnitId);
        },
        'description' => 'Send usage for each root OU to Stripe. Defaults to todays usage',
        'arguments' => [
            'from' => [
                'required' => false,
                'description' => 'When to get usage from. Defaults to today.',
            ],
            'to' => [
                'required' => false,
                'description' => 'When to get usage to. Defaults to today.',
            ],
            'organisationUnitId' => [
                'required' => false,
                'description' => 'The OU to send usage for. Defaults to all.'
            ]
        ],
        'options' => [],
    ],
];