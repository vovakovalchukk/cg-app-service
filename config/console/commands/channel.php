<?php
use CG\Account\Client\Entity as Account;
use CG\Account\Client\Filter as AccountFilter;
use CG\Account\Client\Service as AccountService;
use CG\CGLib\Gearman\WorkerFunction\PushAllStockForAccount;
use CG\CGLib\Gearman\Workload\PushAllStockForAccount as PushAllStockForAccountWorkload;
use CG\Channel\Command\Listing\Import as ListingImport;
use CG\Channel\Command\Message\Download as MessageDownload;
use CG\Channel\Command\Order\Download as OrderDownload;
use CG\Channel\Command\Order\Generator as OrderGenerator;
use CG\Gearman\Client as GearmanClient;
use CG\Gearman\WrapperWorkload as GearmanPriority;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
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
            /** @var GearmanClient $gearman */
            $gearman = $di->get(GearmanClient::class);
            if ($input->getOption('lowPriority')) {
                $gearman->setPriority(GearmanPriority::LOW_PRIORITY);
            } else if ($input->getOption('highPriority')) {
                $gearman->setPriority(GearmanPriority::HIGH_PRIORITY);
            } else {
                $gearman->setPriority(GearmanPriority::NORMAL_PRIORITY);
            }

            /** @var ListingImport $command */
            $command = $di->get(ListingImport::class);
            $command->importListings($input->getOption('automated'));
        },
        'description' => 'Fetch all the sales account and use a factory to generate the Gearman Jobs for each to download listings',
        'arguments' => [
        ],
        'options' => [
            'automated' => [
                'description' => 'Marks the jobs as being generated as part of an automated process',
            ],
            'lowPriority' => [
                'description' => 'Queue generated jobs at low priority',
            ],
            'highPriority' => [
                'description' => 'Queue generated jobs at high priority',
            ],
        ],
        'modulus' => true
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
        ],
        'modulus' => true
    ],
    'channel:pushStock' => [
        'command' => function(InputInterface $input, OutputInterface $output) use($di) {
                /** @var AccountService $accountService */
                $accountService = $di->get(AccountService::class);
                /** @var GearmanClient $gearmanClient */
                $gearmanClient = $di->get(GearmanClient::class);

                $filter = (new AccountFilter('all', 1))
                    ->setActive(true)
                    ->setDeleted(false)
                    ->setStockManagement(true)
                    ->setChannel($input->getOption('channel'))
                    ->setId($input->getArgument('accountId'));

                try {
                    $accounts = $accountService->fetchByFilter($filter);
                    $output->writeln(sprintf('Generating jobs to push stock for %d accounts', $accounts->count()));

                    /** @var Account $account */
                    foreach ($accounts as $account) {
                        $output->writeln(sprintf('Generating jobs for account %d', $account->getId()));
                        $gearmanClient->doBackground(
                            PushAllStockForAccount::FUNCTION_NAME,
                            serialize(new PushAllStockForAccountWorkload($account)),
                            PushAllStockForAccount::FUNCTION_NAME . '' . $account->getId()
                        );
                    }

                    $output->writeln('Jobs generated');
                } catch (NotFound $exception) {
                    $output->writeln('No accounts match passed parameters');
                }
        },
        'description' => 'Push all stock for the selected account',
        'arguments' => [
            'accountId' => [
                'description' => 'List of account ids to push stock for, default is all',
                'required' => false,
                'array' => true,
                'default' => [],
            ],
        ],
        'options' => [
            'channel' => [
                'description' => 'Restrict accounts to the specified channels',
                'value' => true,
                'required' => true,
                'array' => true,
                'default' => [],
            ],
        ],
    ],
];
