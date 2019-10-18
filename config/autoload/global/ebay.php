<?php
use CG\Ebay\ListingImport as EbayListingImport;
use CG\Ebay\Gearman\Generator\RemoveItemRedisKey as RemoveItemRedisKeyGenerator;

return [
    'di' => [
        'instance' => [
            EbayListingImport::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ],
            RemoveItemRedisKeyGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'ebayGearmanClient'
                ]
            ]
        ],
    ],
];