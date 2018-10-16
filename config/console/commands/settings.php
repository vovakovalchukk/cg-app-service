<?php

use CG\Settings\InvoiceMapping\Command\CopyDataFromInvoiceSettingsToMapping;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'settings:migrate-invoice-settings-to-mapping' => [
        'description' => '@TODO: fill this in!',
        'arguments' => [],
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            /** @var CopyDataFromInvoiceSettingsToMapping $command */
            $command = $di->get(CopyDataFromInvoiceSettingsToMapping::class);
            $command();
        },
    ]
];
