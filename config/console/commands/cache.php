<?php
use CG\Cache\Command\RequeueStaleProcessingQueues;
use CG\Cache\Command\ValidateCollection;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'cache:validateCollections' => [
        'command' => function(InputInterface $input, OutputInterface $output) use ($di) {
            /** @var ValidateCollection $command */
            $command = $di->newInstance(ValidateCollection::class);
            $command(
                $output,
                $input->getOption('batchSize'),
                $input->getArgument('timeout'),
                $input->getArgument('maxProcess')
            );
        },
        'description' => 'Validate all fetched collections, removing any collection from cache that fails',
        'arguments' => [
            'timeout' => [
                'description' => 'The length of time we should block for when waiting for a new collection to process. If set to 0, we will wait indefinetly. Default: 30 seconds',
                'default' => 30,
            ],
            'maxProcess' => [
                'description' => 'The maximum number of collections to validate. If not set, will keep processing until the qureue timeout is hit.',
            ],
        ],
        'options' => [
            'batchSize' => [
                'description' => 'The maximum number of collections to validate before creating a new processing queue. Default: 100',
                'value' => true,
                'default' => 100,
            ],
        ],
    ],
    'cache:requeueStaleCollectionValidationProcessingQueues' => [
        'command' => function(InputInterface $input) use ($di) {
            /** @var RequeueStaleProcessingQueues $command */
            $command = $di->newInstance(RequeueStaleProcessingQueues::class);
            $command($input->getArgument('age'));
        },
        'description' => 'Requeue any stale collection validation processing queues',
        'arguments' => [
            'age' => [
                'description' => 'The minimum age of a processing queue before it is considered to be stale. Default: 1 hour',
            ],
        ],
    ],
];
