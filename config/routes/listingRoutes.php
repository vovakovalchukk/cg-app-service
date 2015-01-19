<?php
// Listing
use CG\Controllers\Listing\Entity as ListingEntityController;
use CG\Controllers\Listing\Collection as ListingCollectionController;
use CG\InputValidation\Listing\Entity as ListingEntityValidation;
use CG\InputValidation\Listing\Filter as ListingCollectionValidation;
use CG\Listing\Entity as ListingEntity;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Service\Service as ListingService;

// Unimported Listing
use CG\Controllers\Listing\Unimported\Entity as UnimportedListingEntityController;
use CG\Controllers\Listing\Unimported\Collection as UnimportedListingCollectionController;
use CG\InputValidation\Listing\Unimported\Entity as UnimportedListingEntityValidation;
use CG\InputValidation\Listing\Unimported\Filter as UnimportedListingCollectionValidation;
use CG\Listing\Unimported\Entity as UnimportedListingEntity;
use CG\Listing\Unimported\Mapper as UnimportedListingMapper;
use CG\Listing\Unimported\Service as UnimportedListingService;

use CG\Slim\Versioning\Version;

return [
    '/listing' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ListingCollectionController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'ListingCollection',
        'validation' => [
            'filterRules' => ListingCollectionValidation::class,
            'dataRules' => ListingEntityValidation::class
        ],
        'entityRoute' => '/listing/:listingId',
        "version" => new Version(1, 3)
    ],
    '/listing/:listingId' => [
        'controllers' => function($listingId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ListingEntityController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($listingId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'ListingEntity',
        'validation' => [
            "dataRules" => ListingEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ListingMapper::class,
            'entityClass' => ListingEntity::class,
            'serviceClass' => ListingService::class
        ],
        "version" => new Version(1, 3)
    ],
    '/unimportedListing' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(UnimportedListingCollectionController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'UnimportedListingCollection',
        'validation' => [
            'filterRules' => UnimportedListingCollectionValidation::class,
            'dataRules' => UnimportedListingEntityValidation::class
        ],
        "version" => new Version(1, 3)
    ],
    '/unimportedListing/:listingId' => [
        'controllers' => function($listingId) use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(UnimportedListingEntityController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($listingId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'UnimportedListingEntity',
        'validation' => [
            "dataRules" => UnimportedListingEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => UnimportedListingMapper::class,
            'entityClass' => UnimportedListingEntity::class,
            'serviceClass' => UnimportedListingService::class,
        ],
        "version" => new Version(1, 3)
    ]
];
