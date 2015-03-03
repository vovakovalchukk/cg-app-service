<?php
set_time_limit(0);
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Product\Command\RemoveThenCorrectImportedProducts;

return [
    'removeThenCorrectImportedProducts' => [
        'description' => "Inspect a workload from a queue and requeue it",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $removeThenCorrectImportListings = $di->get(RemoveThenCorrectImportedProducts::class, ['outputter' => $output]);
            $removeThenCorrectImportListings();
        }
    ]
];
