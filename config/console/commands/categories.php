<?php
use CG\Product\Category\Command\UpdateNullVersionCategories;
use CG\Stdlib\Exception\Runtime\NotFound;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'categories:updateNullVersion' => [
        'command' => function (InputInterface $input, OutputInterface $output) use ($di) {
            $channelName = $input->getArgument('channelName');
            $marketplace = $input->getArgument('marketplace');

            /**
             * @var UpdateNullVersionCategories $command
             */
            $command = $di->get(UpdateNullVersionCategories::class);
            $command($output, $channelName, $marketplace);
        },
        'description' => 'Remove null version in categories which channels have been moved to categoryVersionMapper',
        'arguments' => [
            'channelName' => [
                'required' => true
            ],
            'marketplace' => [
                'required' => true,
            ]
        ],
        'options' => [],
    ],
];