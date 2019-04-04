<?php

use CG\Walmart\Gearman\Generator\FetchInventoryReportForAccount;

return [
    'di' => [
        'instance' => [
            FetchInventoryReportForAccount::class => [
                'parameters' => [
                    'gearmanClient' => 'walmartGearmanClient'
                ],
            ]
        ]
    ]
];