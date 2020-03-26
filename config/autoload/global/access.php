<?php

use CG\OrganisationUnit\Access\StorageInterface as OrganisationUnitAccessStorage;
use CG\OrganisationUnit\Access\Storage\Api as OrganisationUnitAccessApiStorage;

return [
    'di' => [
        'instance' => [
            OrganisationUnitAccessApiStorage::class => [
                'parameter' => [
                    'client' => 'directory_guzzle'
                ],
            ],
            'preferences' => [
                OrganisationUnitAccessStorage::class => OrganisationUnitAccessApiStorage::class,
            ],
        ],
    ],
];