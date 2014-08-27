<?php
use CG\Controllers\Location\Location;
use CG\Controllers\Product\Product\Collection as ProductCollection;

use CG\InputValidation\Product\Entity as ProductEntityValidation;
use CG\InputValidation\Product\Filter as ProductCollectionValidation;

use CG\Product\Entity as ProductEntity;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Service as ProductService;

use CG\Slim\Versioning\Version;

return [
    '/location' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(LocationCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'LocationCollection',
        'validation' => [
            'filterRules' => LocationCollectionValidation::class,
            'dataRules' => LocationEntityValidation::class
        ]
    ],
    '/location/:locationId' => [
        'controllers' => function($locationId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Location::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($locationId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'LocationEntity',
        'validation' => [
            "dataRules" => LocationEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => LocationMapper::class,
            'entityClass' => LocationEntity::class,
            'serviceClass' => LocationService::class
        ]
    ]
];