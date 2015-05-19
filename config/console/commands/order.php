<?php
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use CG\Order\Shared\Command\UpdateAllItemsTax;

return [
    'order:updateAllItemsTax' => [
        'description' => "Update the calculatedTaxPercentage on all Order Items across all OUs",
        'options' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $command = $di->get(UpdateAllItemsTax::class);
            $command();
        }
    ]
];
