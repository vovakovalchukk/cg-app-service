<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use CG\Channel\Command\Listing\Import as ListingImport;
use CG\Channel\Command\Order\Generator as OrderGenerator;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'channel:downloadOrders' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = 'ekm';
            $from = $input->getArgument('from');
            $to = $input->getArgument('to');

            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel, $from, $to);
        },
        'description' => 'Fetch all ekm accounts and triggers order download jobs for the last 30days',
        'arguments' => array(
            'from' => array(
                'required' => false,
                'default' => null
            ),
            'to' => array(
                'required' => false,
                'default' => null
            )
        ),
        'options' => array(

        )
    )
);
