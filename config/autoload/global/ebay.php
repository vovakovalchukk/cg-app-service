<?php
use CG\Ebay\ListingImport as EbayListingImport;
use CG\Ebay\Gearman\Generator\Listing\SubmitImport as ListingSubmitImportGenerator;

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