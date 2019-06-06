<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'ekm:pollForOrders' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = 'ekm';
            $from = $input->getArgument('from');
            $to = $input->getArgument('to');
            $lowPriority = $input->getOption('lowPriority');
            $highPriority = $input->getOption('highPriority');

            /** @var OrderDownload $command */
            $command = $di->get(OrderDownload::class);
            $command->downloadOrders($channel, $from, $to, null, false, $lowPriority, $highPriority);
        },
        'description' => 'Fetch all ekm accounts and triggers order download jobs for the last 30days',
        'modulus' => true,
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
            'lowPriority' => [
                'description' => 'Queue generated jobs at low priority',
            ],
            'highPriority' => [
                'description' => 'Queue generated jobs at high priority',
            ]
        )
    )
);
