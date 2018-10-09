<?php
use CG\Amazon\ListingImport as AmazonListingImport;

return [
    'di' => [
        'instance' => [
            AmazonListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'amazonGearmanClient'
                ]
            ]
        ],
    ],
];