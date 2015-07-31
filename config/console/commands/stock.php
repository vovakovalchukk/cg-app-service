<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Stock\Command\AuditAllStock;
use CG\Stock\Command\ZeroNegativeStock;
use CG\Stock\Command\ProcessAudit;
use CG\Stock\Gearman\WorkerFunction\ProcessAudit as StockWorker;
use CG\Stock\Gearman\WorkerFunction\ProcessAdjustmentAudit as AdjustmentWorker;

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
    ]
];
