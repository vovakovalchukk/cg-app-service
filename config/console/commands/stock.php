<?php

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Stock\Command\AuditAllStock;
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
    ]
];
