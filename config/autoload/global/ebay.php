<?php
use CG\Ebay\ListingImport as EbayListingImport;

return [
    'di' => [
        'instance' => [
            EbayListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ]
        ],
    ],
];