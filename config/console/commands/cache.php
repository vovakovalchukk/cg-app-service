<?php
use CG\Cache\Command\ValidateCollection;
use Symfony\Component\Console\Input\InputInterface;
use Zend\Di\Di;

/** @var Di $di */
return [
    'cache:validateCollections' => [
        'command' => function(InputInterface $input) use ($di) {
            /** @var ValidateCollection $command */
            $command = $di->newInstance(ValidateCollection::class);
            $command->processQueue($input->getArgument('timeout'));
        },
        'description' => 'Validate all fetched collections, removing any collection from cache that fails',
        'arguments' => [
            'timeout' => [
                'description' => 'The length of time we should block for when waiting for a new collection to process. If set to 0, we will wait indefinetly. Default: 30 seconds',
                'default' => 30,
            ],
        ],
    ],
    'cache:requeueStaleCollectionValidationProcessingQueues' => [
        'command' => function(InputInterface $input) use ($di) {
            /** @var ValidateCollection $command */
            $command = $di->newInstance(ValidateCollection::class);
            $command->requeueStaleProcessingQueues($input->getArgument('age'));
        },
        'description' => 'Requeue any stale collection validation processing queues',
        'arguments' => [
            'age' => [
                'description' => 'The minimum age of a processing queue before it is considered to be stale. Default: 1 hour',
            ],
        ],
    ],
];
