<?php

use CG\Transaction\Predis\ClearStaleTransaction;

return [
    'di' => [
        'definition' => [
            'class' => [
                ClearStaleTransaction::class => [
                    'methods' => [
                        'setLuaFileDirectory' => [
                            'directory' => [
                                'required' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'instance' => [
            ClearStaleTransaction::class => [
                'injections' => [
                    'setLuaFileDirectory' => [
                        'directory' => PROJECT_ROOT . '/lua/',
                    ],
                ],
            ],
        ],
    ],
];