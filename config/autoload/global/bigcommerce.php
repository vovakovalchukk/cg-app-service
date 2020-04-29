<?php
use CG\Bigcommerce\ListingImport as BigcommerceListingImport;

return [
    'di' => [
        'instance' => [
            BigcommerceListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'bigcommerceGearmanClient'
                ]
            ]
        ],
    ],
];