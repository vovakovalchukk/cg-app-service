<?php
use CG\Log\Shared\StorageInterface;
use CG\Log\Shared\Storage\Redis\Channel as RedisChannelStorage;
use CG\Redis\Channel\Publisher;
use CG\Log\LogMessages\Storage as LogMessagesService;
use CG\Log\LogMessages\Collection as LogMessagesCollection;
use CG\Stdlib\Log\LoggerInterface as Logger;
use CG\Log\Logger as CGLogger;
use CG\Log\Shared\FormatterInterface as Formatter;
use CG\Log\Shared\Formatter\ArrayDump;

return [
    'di' => [
        'instance' => [
            'aliases' => [
                'LogStorage' => RedisChannelStorage::class,
                'LogPublisher' => Publisher::class,
                'LogMessagesStorage' => LogMessagesService::class,
            ],
            'preferences' => [
                Logger::class => CGLogger::class,
                StorageInterface::class => 'LogStorage',
                Formatter::class => ArrayDump::class,
            ],
            'LogStorage' => [
                'parameters' => [
                    'publisher' => 'LogPublisher',
                ],
            ],
            'LogPublisher' => [
                'parameters' => [
                    'channel' => 'php-log',
                ],
            ],
            'LogMessagesStorage' => [
                'parameters' => [
                    'logMessagesCollection' => LogMessagesCollection::class,
                ],
            ],
        ],
    ],
];