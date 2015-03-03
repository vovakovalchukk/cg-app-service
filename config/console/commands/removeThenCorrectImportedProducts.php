<?php
use Symfony\Component\Console\Input\InputInterface;
use CG\Product\Command\RemoveThenCorrectImportedProducts;

return [
    'removeThenCorrectImportedProducts' => [
        'description' => "Inspect a workload from a queue and requeue it",
        'options' => [],
        'command' => function (InputInterface $input) use ($di) {
            $removeThenCorrectImportListings = $di->get(RemoveThenCorrectImportedProducts::class);
            $removeThenCorrectImportListings();
        }
    ]
];
