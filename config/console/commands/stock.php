<?php

use CG\Stock\Command\AuditAllStock;
use CG\Stock\Command\CreateMissingStock;
use CG\Stock\Command\MigrateStockAuditAdjustments;
use CG\Stock\Command\ProcessAudit;
use CG\Stock\Command\RemoveDuplicateStock;
use CG\Stock\Command\ZeroNegativeStock;
use CG\Stock\Gearman\WorkerFunction\ProcessAdjustmentAudit as AdjustmentWorker;
use CG\Stock\Gearman\WorkerFunction\ProcessAudit as StockWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'stock:processAudit' => [
        'command' => function (InputInterface $input) use ($di) {
            $setSize = $input->getArgument('setSize');
            $command = $di->get(ProcessAudit::class);
            $command(StockWorker::FUNCTION_NAME, $setSize);
        },
        'description' => 'Generate processStockAudit jobs',
        'arguments' => [
            'setSize' => [
                'required' => false,
                'default' => 100
            ]
        ],
        'options' => [

        ]
    ],
    'stock:processAdjustmentAudit' => [
        'command' => function (InputInterface $input) use ($di) {
            $setSize = $input->getArgument('setSize');
            $command = $di->get(ProcessAudit::class);
            $command(AdjustmentWorker::FUNCTION_NAME, $setSize);
        },
        'description' => 'Generate processStockAdjustmentAudit jobs',
        'arguments' => [
            'setSize' => [
                'required' => false,
                'default' => 100
            ]
        ],
        'options' => [

        ]
    ],
    'stock:migrateAdjustmentAudit' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            $command = $di->get(MigrateStockAuditAdjustments::class);
            $command($output, $input->getArgument('timeFrame'), $input->getOption('limit') ?? 1000);
        },
        'description' => 'Migrate stock audit adjustments to archive storage',
        'arguments' => [
            'timeFrame' => [
                'description' => 'Migrate any stock adjustments alter than date',
                'required' => false,
                'default' => '1 day ago',
            ],
        ],
        'options' => [
            'limit' => [
                'description' => 'Restricts the number of stock adjustments to migrate in this batch <comment>[default: 1000]</comment>',
                'value' => true,
                'required' => true,
            ],
        ],
    ],
    'stock:auditAll' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $output->writeln('Starting Audit All Stock command');
            $command = $di->get(AuditAllStock::class);
            $command();
            $output->writeln('Done. Check the logs for details.');
        },
        'description' => 'Create an entry in the stockLog table for all stock in the system',
        'arguments' => [
        ],
        'options' => [

        ]
    ],
    'stock:zeroNegativeStock' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $dryRun = !$input->getOption('wet-run');
            $command = $di->get(ZeroNegativeStock::class, ['output' => $output]);
            $command($dryRun);
        },
        'description' => 'Identify negative stock levels and zero them',
        'arguments' => [
        ],
        'options' => [
            'wet-run' => [
                'description' => 'Wet run, i.e. do the stock changes - without this it defaults to a dry run'
            ]
        ],
    ],
    'stock:createMissingStock' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $dryRun = !$input->getOption('wet-run');
            $output->writeLn('Starting createMissingStock command' . ($dryRun ? ' (DRY RUN)' : ''));
            $command = $di->get(CreateMissingStock::class);
            $affected = $command($dryRun);
            $output->writeLn('Finished createMissingStock command' . ($dryRun ? ' (DRY RUN)' : '') . ', ' . $affected . ' stock created. Check the logs for details.');
        },
        'description' => 'Identify products that should have a stock record but dont and create them',
        'arguments' => [
        ],
        'options' => [
            'wet-run' => [
                'description' => 'Wet run, i.e. do the stock changes - without this it defaults to a dry run'
            ]
        ],
    ],
    'stock:removeDuplicateStock' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $dryRun = !$input->getOption('wet-run');
            $output->writeLn('Starting removeDuplicateStock command' . ($dryRun ? ' (DRY RUN)' : ''));
            $command = $di->get(RemoveDuplicateStock::class);
            $affected = $command($dryRun);
            $output->writeLn('Finished removeDuplicateStock command' . ($dryRun ? ' (DRY RUN)' : '') . ', ' . $affected . ' stock deleted. Check the logs for details.');
        },
        'description' => 'Identify duplicated stock records and delete them',
        'arguments' => [
        ],
        'options' => [
            'wet-run' => [
                'description' => 'Wet run, i.e. do the stock changes - without this it defaults to a dry run'
            ]
        ],
    ],
];
