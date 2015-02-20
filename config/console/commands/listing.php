<?php
use CG\Listing\Command\DeleteUnimportedListings as Command;
use Symfony\Component\Console\Input\InputInterface;

return [
    'listing:deleteListingsForChannel' => array(
        'command' => function (InputInterface $input) use ($di) {
            $channel = $input->getArgument('channel');

            $command = $di->get(Command::class);
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
];
