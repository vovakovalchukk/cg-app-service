<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Product\Command\RemoveThenCorrectImportedProducts;

return [
    'removeThenCorrectImportedProducts' => [
        'description' => "Remove then re-import parent products that do not have any productAttributes",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $removeThenCorrectImportListings = $di->get(RemoveThenCorrectImportedProducts::class, ['outputter' => $output]);
            $removeThenCorrectImportListings();
        }
    ]
];
