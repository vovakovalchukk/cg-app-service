<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'channel:downloadOrders' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');
            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel);
        },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get orders from the respective channel.',
        'arguments' => array(
            'channel' => array(
                'required' => true
            )
        ),
        'options' => array(

        )
    )
);
