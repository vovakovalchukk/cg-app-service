<?php
use CG\Listing\Command\AddSkusToListings as AddSkusToListingsCommand;
use CG\Listing\Command\CorrectPendingListingsStatusFromSiblingListings as CorrectPendingListingsStatusFromSiblingListingsCommand;
use CG\Listing\Command\DeleteUnimportedListings as DeleteUnimportedListingsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    'listing:deleteListingsForChannel' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');

            $command = $di->get(DeleteUnimportedListingsCommand::class);
            $command([$channel]);
        },
        'description' => 'Delete unimported listings for channel',
        'arguments' => [
            'channel' => [
                'required' => true
            ]
        ],
        'options' => []
    ),
    'listing:correctPendingListingsStatusFromSiblingListings' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $output->writeln('Starting correctPendingListingsStatusFromSiblingListings command');
            $channel = $input->getArgument('channel');
            $command = $di->get(CorrectPendingListingsStatusFromSiblingListingsCommand::class);
            $affected = $command($channel);
            $output->writeln('Done, ' . $affected . ' Listings were affected, see the logs for details');
        },
        'description' => 'Find pending listings for a given channel and, if possible, copy their correct status from a sibling Listing',
        'arguments' => [
            'channel' => [
                'required' => true
            ]
        ],
        'options' => []
    ],
    'listing:addSkusToListings' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $output->writeln('Starting addSkusToListings command');
            $command = $di->get(AddSkusToListingsCommand::class);
            $affected = $command();
            $output->writeln('Done, ' . $affected . ' Listings were affected, see the logs for details');
        },
        'description' => 'Adds missing SKUs on to Listings from their Products',
        'arguments' => [],
        'options' => []
    ],
];
