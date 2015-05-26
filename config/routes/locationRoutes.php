<?php
use CG\Controllers\Location\Entity as Location;
use CG\Controllers\Location\Collection as LocationCollection;

use CG\InputValidation\Location\Entity as LocationEntityValidation;
use CG\InputValidation\Location\Filter as LocationCollectionValidation;

use CG\Location\Entity as LocationEntity;
use CG\Location\Mapper as LocationMapper;
use CG\Location\Service as LocationService;

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
        'entityRoute' => '/location/:locationId',
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