<?php

use CG\Order\Command\CalculateOrderWeight;
use CG\Order\Command\RedactOrders as RedactOrdersCommand;
use CG\Order\Command\RestoreRedactedOrder as RestoreRedactedOrderCommand;
use CG\Order\Shared\Command\ApplyMissingStockAdjustmentsForCancDispRefOrders;
use CG\Order\Shared\Command\AutoArchiveOrders;
use CG\Order\Shared\Command\CorrectStockOfItemsWithIncorrectStockManagedFlag;
use CG\Order\Shared\Command\DetermineAndUpdateDispatchableOrders;
use CG\Order\Shared\Command\ReSyncOrderCounts;
use CG\Order\Shared\Command\UpdateAllItemsImages;
use CG\Order\Shared\Command\UpdateAllItemsTax;
use CG\Stdlib\DateTime as StdlibDateTime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'order:updateAllItemsTax' => [
        'description' => "Update the calculatedTaxPercentage on all Order Items across all OUs",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $command = $di->get(UpdateAllItemsTax::class);
            $command();
        }
    ],
    'order:updateAllItemsImages' => [
        'description' => "Update the imageIds on all Order Items across all OUs",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di)
        {
            $output->writeln('updateAllItemsImages started');
            $command = $di->get(UpdateAllItemsImages::class);
            $command();
            $output->writeln('Done. Check the logs for details, log code: ' . UpdateAllItemsImages::LOG_CODE . '</info>');
        }
    ],
    'order:correctStockOfItemsWithIncorrectStockManagedFlag' => [
        'description' => "Identify and fix Items which had their stockManaged flag incorrectly set to false "
            . "and recreate the subsequently missing stock adjustments for them.",
        'options' => [
            'dry-run' => [
                'description' => 'Dry run - will gather the data but not actually alter the Items or Stock'
            ]
        ],
        'arguments' => [
            'start' => [
                'required' => false
            ]
        ],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $start = $input->getArgument('start');
            if ($start) {
                $start = new \DateTime($start);
            }
            $dryRun = $input->getOption('dry-run');
            $command = $di->get(CorrectStockOfItemsWithIncorrectStockManagedFlag::class);
            $command($start, $dryRun);
        }
    ],
    'order:applyMissingStockAdjustmentsForCancDispRefOrders' => [
        'description' => "Identify Orders in a given period that seemingly did not apply stock adjustments when they were cancelled, dispatched or refunded and apply those adjustments",
        'options' => [
            'dry-run' => [
                'description' => 'Dry run - will gather the data but not actually alter the Stock'
            ]
        ],
        'arguments' => [
            'start' => [
                'required' => true
            ],
            'end' => [
                'required' => true
            ]
        ],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $start = new \DateTime($input->getArgument('start'));
            $end = new \DateTime($input->getArgument('end'));
            $dryRun = $input->getOption('dry-run');
            $startString = $start->format(StdlibDateTime::FORMAT);
            $endString = $end->format(StdlibDateTime::FORMAT);
            $dryRunString = ($dryRun ? '(dry run)' : '');
            $output->writeln('<info>'.vsprintf(
                ApplyMissingStockAdjustmentsForCancDispRefOrders::LOG_MSG_INVOKED,
                [$startString, $endString, $dryRunString]
            ).'</info>');

            $command = $di->get(ApplyMissingStockAdjustmentsForCancDispRefOrders::class);
            $command($start, $end, $dryRun);

            $output->writeln('<info>Done. Check the logs for details, log code: ' . ApplyMissingStockAdjustmentsForCancDispRefOrders::LOG_CODE . '</info>');
        }
    ],
    'order:reSyncOrderCounts' => [
        'description' => "Correct the order counts for all OrganisationUnits in the system",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            if ($output->getVerbosity() > OutputInterface::VERBOSITY_QUIET) {
                $output->writeln('Starting re-sync of order counts');
            }
            
            $command = $di->get(ReSyncOrderCounts::class);
            $affected = $command();

            if ($output->getVerbosity() > OutputInterface::VERBOSITY_QUIET) {
                $output->writeln('Done, ' . $affected . ' OUs affected');
            }
        }
    ],
    'order:autoArchive' => [
        'description' => "Archive any orders older than the users configured threshold",
        'options' => [],
        'modulus' => true,
        'command' => function() use ($di)
        {
            $command = $di->get(AutoArchiveOrders::class);
            $command();
        }
    ],
    'order:calculateOrderWeights' => [
        'description' => 'Calculate the weight of all orders based on the associated product weights',
        'options' => [
            'ou' => [
                'description' => 'Only update orders belonging to selected ous',
                'value' => true,
                'required' => true,
                'array' => true,
            ],
            'account' => [
                'description' => 'Only update orders belonging to selected accounts',
                'value' => true,
                'required' => true,
                'array' => true,
            ],
            'includeArchived' => [
                'description' => 'Also update archived orders',
                'value' => false,
            ],
        ],
        'command' => function(InputInterface $input, OutputInterface $output) use($di) {
            ($di->get(CalculateOrderWeight::class, ['output' => $output]))(
                $input->getOption('ou'),
                $input->getOption('account'),
                (bool) $input->getOption('includeArchived')
            );
        }
    ],
    'order:determineAndUpdateDispatchableOrders' => [
        'description' => 'Determine which orders are dispatchable for a list of root OU ids and SKUs and updates them',
        'arguments' => [
            'rootOrganisationUnit' => [
                'required' => false,
                'default' => null
            ]
        ],
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $rootOrganisationUnitId = ((string)$input->getArgument('rootOrganisationUnit') ?: null);

            $command = $di->get(DetermineAndUpdateDispatchableOrders::class, ['output' => $output]);
            $command($rootOrganisationUnitId);
        }
    ],
    'order:redactOrders' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var RedactOrdersCommand $command */
            $command = $di->get(RedactOrdersCommand::class);
            $command(
                $output,
                $input->getArgument('channel'),
                $input->getArgument('time'),
                $input->getOption('limit')
            );
        },
        'description' => 'Generates gearman jobs to redacts pii from orders if they are older than the supplied age',
        'arguments' => [
            'channel' => [
                'description' => 'The channel to match orders for',
                'required' => true,
            ],
            'time' => [
                'description' => sprintf(
                    'A DateTime-compatible relative time string, default is "%s"',
                    RedactOrdersCommand::DEFAULT_DATE
                ),
                'required' => false,
            ],
        ],
        'options' => [
            'limit' => [
                'description' => 'Limit the number of jobs that can be generated',
                'value' => true,
                'required' => true,
            ],
        ],
    ],
    'order:restoreRedactedOrder' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var RestoreRedactedOrderCommand $command */
            $command = $di->get(RestoreRedactedOrderCommand::class);
            $command(
                $output,
                $input->getArgument('orderId'),
                $input->getArgument('restoreUntil')
            );
        },
        'description' => 'Restores redacted pii for an order',
        'arguments' => [
            'orderId' => [
                'description' => 'The id of the order that the redacted data should be restored for',
                'required' => true,
            ],
            'restoreUntil' => [
                'description' => sprintf(
                    'A DateTime-compatible relative time string, default is "%s"',
                    RestoreRedactedOrderCommand::DEFAULT_RESTORE_UNTIL
                ),
                'required' => false,
            ],
        ],
        'options' => [],
    ],
];
