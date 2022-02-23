<?php
use CG\Channel\Listing\Import\PermissionService;

return [
    'di' => [
        'instance' => [
            PermissionService::class => [
                'parameters' => [
                    'accountIdBlacklist' => [
                        '13838' => true
                    ]
                ]
            ]
        ],
    ],
];