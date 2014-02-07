<?php
use CG\App\Command\OrderDownload;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'downloadOrders:command' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');
            $getToTime = $input->getArgument('getToTime') ? $input->getArgument('getToTime') : 'now';

            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel, $getToTime);
        },
        'description' => 'Fetch all accounts for the provided channel then generate Gearman jobs to get orders from the respective channel.',
        'arguments' => array(
            'channel' => array(
                'required' => true
            ),
            'getToTime' => array(
                'required' => false
            )
        ),
        'options' => array(

        )
    )
);