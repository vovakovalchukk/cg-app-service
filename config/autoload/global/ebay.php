<?php
use CG\Ebay\Gearman\Generator\Listing\SubmitImport as ListingSubmitImportGenerator;
use CG\Ebay\ListingImport as EbayListingImport;

return [
    'di' => [
        'instance' => [
            EbayListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ],
            ListingSubmitImportGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ]
        ],
    ],
];