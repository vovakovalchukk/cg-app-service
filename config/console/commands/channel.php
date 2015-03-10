<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use CG\Channel\Command\Listing\Import as ListingImport;
use CG\Channel\Command\Order\Generator as OrderGenerator;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'channel:downloadOrders' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');
            $from = $input->getArgument('from');
            $to = $input->getArgument('to');

            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel, $from, $to);
        },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get orders from the respective channel.',
        'arguments' => array(
            'channel' => array(
                'required' => true
            ),
            'from' => array(
                'required' => false,
                'default' => null
            ),
            'to' => array(
                'required' => false,
                'default' => null
            ),
            'accountId' => array(
                'required' => false
            )
        ),
        'options' => array(

        )
    ),
    'channel:importListings' => [
        'command' => function (InputInterface $input) use ($di) {
            $command = $di->get(ListingImport::class);
            $command->importListings();
        },
        'description' => 'Fetch all the sales account and use a factory to generate the Gearman Jobs for each to download listings',
        'arguments' => [
        ],
        'options' => [
        ]
    ],
    'channel:generateOrders' => [
        'command' => function(InputInterface $input) use ($di) {
            /**
             * @var $command OrderGenerator
             */
            $command = $di->get(OrderGenerator::class);
            $command->generateOrders(
                $input->getArgument('accountId'),
                $input->getArgument('numberOfOrders')
            );
        },
        'description' => 'Generates orders for the selected account and saves them against the accounts OU',
        'arguments' => [
            'accountId' => [
                'required' => true,
                'description' => 'Account to generate orders for',
            ],
            'numberOfOrders' => [
                'required' => false,
                'description' => 'Number of orders to generate for account',
                'default' => 100,
            ],
        ]
    ],
);
