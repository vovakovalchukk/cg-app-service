<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use CG\Channel\Command\Message\Download as MessageDownload;
use CG\Channel\Command\Listing\Import as ListingImport;
use CG\Channel\Command\Order\Generator as OrderGenerator;
use Symfony\Component\Console\Input\InputInterface;

return [
    'channel:downloadOrders' => [
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');
            $from = $input->getArgument('from');
            $to = $input->getArgument('to');
            $accountId = $input->getArgument('accountId');
            $logFoundOrders = $input->getOption('logFoundOrders');
            $lowPriority = $input->getOption('lowPriority');
            $highPriority = $input->getOption('highPriority');

            /**
             * @var OrderDownload $command
             */
            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel, $from, $to, $accountId, $logFoundOrders, $lowPriority, $highPriority);
        },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get orders from the respective channel.',
        'arguments' => [
            'channel' => [
                'required' => true
            ],
            'from' => [
                'required' => false,
                'default' => null
            ],
            'to' => [
                'required' => false,
                'default' => null
            ],
            'accountId' => [
                'required' => false
            ]
        ],
        'options' => [
            'logFoundOrders' => [
                'description' => 'logs errors if orders are discovered',
            ],
            'lowPriority' => [
                'description' => 'Queue generated jobs at low priority',
            ],
            'highPriority' => [
                'description' => 'Queue generated jobs at high priority',
            ]
        ],
        'modulus' => true
    ],
    'channel:importListings' => [
        'command' => function (InputInterface $input) use ($di) {
            $command = $di->get(ListingImport::class);
            $command->importListings();
        },
        'description' => 'Fetch all the sales account and use a factory to generate the Gearman Jobs for each to download listings',
        'arguments' => [
        ],
        'options' => [
        ]
    ],
    'channel:generateOrders' => [
        'command' => function(InputInterface $input) use ($di) {
            /**
             * @var $command OrderGenerator
             */
            $command = $di->get(OrderGenerator::class);
            $command->generateOrders(
                $input->getArgument('accountId'),
                $input->getArgument('numberOfOrders')
            );
        },
        'description' => 'Generates orders for the selected account and saves them against the accounts OU',
        'arguments' => [
            'accountId' => [
                'required' => true,
                'description' => 'Account to generate orders for',
            ],
            'numberOfOrders' => [
                'required' => false,
                'description' => 'Number of orders to generate for account',
                'default' => 100,
            ],
        ]
    ],
    'channel:downloadMessages' => [
        'command' => function (InputInterface $input) use ($di) {
                $channel = $input->getArgument('channel');
                $from = $input->getArgument('from');
                $to = $input->getArgument('to');
                $accountId = $input->getArgument('accountId');
                $lowPriority = $input->getOption('lowPriority');
                $highPriority = $input->getOption('highPriority');
                $command = $di->get(MessageDownload::class);
                $command->downloadMessages($channel, $from, $to, $accountId, $lowPriority, $highPriority);
            },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get messages from the respective channel.',
        'arguments' => [
            'channel' => [
                'required' => true
            ],
            'from' => [
                'required' => false,
                'default' => null
            ],
            'to' => [
                'required' => false,
                'default' => null
            ],
            'accountId' => [
                'required' => false
            ]
        ],
        'options' => [
            'lowPriority' => [
                'description' => 'Queue generated jobs at low priority',
            ],
            'highPriority' => [
                'description' => 'Queue generated jobs at high priority',
            ]
        ]
    ],
];
