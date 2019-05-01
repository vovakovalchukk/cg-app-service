<?php
// Listing
use CG\Controllers\Listing\Entity as ListingEntityController;
use CG\Controllers\Listing\Collection as ListingCollectionController;
use CG\InputValidation\Listing\Entity as ListingEntityValidation;
use CG\InputValidation\Listing\Filter as ListingCollectionValidation;
use CG\Listing\Entity as ListingEntity;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Service\Service as ListingService;

// Listing Status History
use CG\Controllers\Listing\StatusHistory\Collection as ListingStatusHistoryCollectionController;
use CG\Controllers\Listing\StatusHistory\Entity as ListingStatusHistoryEntityController;
use CG\InputValidation\Listing\StatusHistory\Filter as ListingStatusHistoryCollectionValidation;
use CG\InputValidation\Listing\StatusHistory\Entity as ListingStatusHistoryEntityValidation;
use CG\Listing\StatusHistory\Mapper as ListingStatusHistoryMapper;
use CG\Listing\StatusHistory\Entity as ListingStatusHistoryEntity;
use CG\Listing\StatusHistory\Service\Service as ListingStatusHistoryService;

// Unimported Listing
use CG\Controllers\Listing\Unimported\Entity as UnimportedListingEntityController;
use CG\Controllers\Listing\Unimported\Collection as UnimportedListingCollectionController;
use CG\InputValidation\Listing\Unimported\Entity as UnimportedListingEntityValidation;
use CG\InputValidation\Listing\Unimported\Filter as UnimportedListingCollectionValidation;
use CG\Listing\Unimported\Entity as UnimportedListingEntity;
use CG\Listing\Unimported\Mapper as UnimportedListingMapper;
use CG\Listing\Unimported\Service as UnimportedListingService;

// Unimported Listing Marketplace
use CG\Controllers\Listing\Unimported\Marketplace as UnimportedListingMarketplaceController;
use CG\InputValidation\Listing\Unimported\Marketplace as UnimportedListingMarketplaceValidation;

use CG\Slim\Versioning\Version;
use Slim\Slim;
use Zend\Di\Di;

/**
 * @var Slim $app
 * @var Di $di
 */
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
        "version" => new Version(1, 9)
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
        "version" => new Version(1, 9)
    ],
    ListingStatusHistoryMapper::URI => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(ListingStatusHistoryCollectionController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ListingStatusHistoryCollection',
        'entityRoute' => ListingStatusHistoryMapper::URI . '/:statusHistoryId',
        'validation' => [
            'filterRules' => ListingStatusHistoryCollectionValidation::class,
            'dataRules' => ListingStatusHistoryEntityValidation::class,
        ],
        'version' => new Version(1, 2),
    ],
    ListingStatusHistoryMapper::URI . '/:statusHistoryId' => [
        'controllers' => function($statusHistoryId) use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(ListingStatusHistoryEntityController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($statusHistoryId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ListingStatusHistoryEntity',
        'validation' => [
            'dataRules' => ListingStatusHistoryEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ListingStatusHistoryMapper::class,
            'entityClass' => ListingStatusHistoryEntity::class,
            'serviceClass' => ListingStatusHistoryService::class,
        ],
        'version' => new Version(1, 2),
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
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'UnimportedListingCollection',
        'entityRoute' => '/unimportedListing/:listingId',
        'validation' => [
            'filterRules' => UnimportedListingCollectionValidation::class,
            'dataRules' => UnimportedListingEntityValidation::class
        ],
        "version" => new Version(1, 6)
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
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'UnimportedListingEntity',
        'validation' => [
            "dataRules" => UnimportedListingEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => UnimportedListingMapper::class,
            'entityClass' => UnimportedListingEntity::class,
            'serviceClass' => UnimportedListingService::class,
        ],
        "version" => new Version(1, 6)
    ],
    '/unimportedListingMarketplace' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(UnimportedListingMarketplaceController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'UnimportedListingMarketplace',
        'validation' => [
            'filterRules' => UnimportedListingMarketplaceValidation::class,
        ],
        'version' => new Version(1, 2)
    ],
];
