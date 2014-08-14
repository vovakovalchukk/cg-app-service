<?php
use CG\Controllers\Listing\Entity as ListingEntityController;
use CG\Controllers\Listing\Collection as ListingCollectionController;

use CG\InputValidation\Listing\Entity as ListingEntityValidation;
use CG\InputValidation\Listing\Filter as ListingCollectionValidation;

use CG\Listing\Entity as ListingEntity;
use CG\Listing\Mapper as ListingMapper;
use CG\Listing\Service as ListingService;

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
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ListingCollection',
        'validation' => [
            'filterRules' => ListingCollectionValidation::class,
            'dataRules' => ListingEntityValidation::class
        ]
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
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ListingEntity',
        'validation' => [
            "dataRules" => ListingEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ListingMapper::class,
            'entityClass' => ListingEntity::class,
            'serviceClass' => ListingService::class
        ]
    ]
];
 