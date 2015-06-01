<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Order\Shared\Command\CorrectStockOfItemsWithIncorrectStockManagedFlag;
use CG\Order\Shared\Command\UpdateAllItemsTax;

return [
    'order:updateAllItemsTax' => [
        'description' => "Update the calculatedTaxPercentage on all Order Items across all OUs",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $command = $di->get(UpdateAllItemsTax::class);
            $command();
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
    ]
];
