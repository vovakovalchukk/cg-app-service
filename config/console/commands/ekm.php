<?php
use CG\Channel\Command\Order\Download as OrderDownload;
use CG\Channel\Command\Listing\Import as ListingImport;
use CG\Channel\Command\Order\Generator as OrderGenerator;
use Symfony\Component\Console\Input\InputInterface;

return array(
    'ekm:pollForOrders' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = 'ekm';
            $from = $input->getArgument('from');
            $to = $input->getArgument('to');
            $lowPriority = $input->getOption('low-priority');

            /**
             * @var OrderDownload $command
             */
            $command = $di->get('EkmOrderDownloadCommand');
            $command->downloadOrders($channel, $from, $to, null, false, $lowPriority);
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
            'low-priority' => [
                'description' => 'Will run gearman jobs as low priority',
            ]
        )
    )
);
