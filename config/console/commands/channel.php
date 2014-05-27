<?php
use CG\Channel\Command\Order\Download as OrderDownload;
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
            )
        ),
        'options' => array(

        )
    )
);
