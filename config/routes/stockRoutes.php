<?php
use CG\Controllers\Stock\Stock;
use CG\Controllers\Stock\Stock\Collection as StockCollection;

use CG\InputValidation\Stock\Entity as StockEntityValidation;
use CG\InputValidation\Stock\Filter as StockCollectionValidation;

use CG\Stock\Entity as StockEntity;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Service as StockService;

use CG\Controllers\Stock\Location\Location;
use CG\Controllers\Stock\Location\Location\Collection as LocationCollection;

use CG\InputValidation\Stock\Location\Entity as LocationEntityValidation;
use CG\InputValidation\Stock\Location\Filter as LocationCollectionValidation;

use CG\Stock\Location\Entity as LocationEntity;
use CG\Stock\Location\Mapper as LocationMapper;
use CG\Stock\Location\Service as LocationService;

return [
    '/stock' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(StockCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'StockCollection',
        'validation' => [
            'filterRules' => StockCollectionValidation::class,
            'dataRules' => StockEntityValidation::class
        ]
    ],
    '/stock/:stockId' => [
        'controllers' => function($stockId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Stock::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($stockId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'StockEntity',
        'validation' => [
            "dataRules" => StockEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => StockMapper::class,
            'entityClass' => StockEntity::class,
            'serviceClass' => StockService::class
        ]
    ],
    '/stock/:stockId/location' => [
        'controllers' => function($stockId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(LocationCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($stockId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'LocationCollection',
        'validation' => [
            'filterRules' => LocationCollectionValidation::class,
            'dataRules' => LocationEntityValidation::class
        ]
    ],
    '/stock/:stockId/location/:locationId' => [
        'controllers' => function($stockId, $locationId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Location::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($stockId, $locationId, $app->request()->getBody())
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
 