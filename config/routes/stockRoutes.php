<?php
// Stock
use CG\Controllers\Stock\Stock;
use CG\Controllers\Stock\Stock\Collection as StockCollection;
use CG\InputValidation\Stock\Entity as StockEntityValidation;
use CG\InputValidation\Stock\Filter as StockCollectionValidation;
use CG\Stock\Entity as StockEntity;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Service as StockService;

// StockLocation
use CG\Controllers\Stock\Location\Location;
use CG\Controllers\Stock\Location\Location\Collection as StockLocationCollection;
use CG\InputValidation\Stock\Location\Entity as LocationEntityValidation;
use CG\InputValidation\Stock\Location\Filter as LocationCollectionValidation;
use CG\Stock\Location\Entity as StockLocationEntity;
use CG\Stock\Location\Mapper as StockLocationMapper;
use CG\Stock\Location\Service as StockLocationService;

// StockLog
use CG\Controllers\Stock\Log\Collection as StockLogCollection;
use CG\InputValidation\Stock\Log\Filter as StockLogFilterValidation;

use CG\Slim\Versioning\Version;

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
        'entityRoute' => '/stock/:stockId',
        'validation' => [
            'filterRules' => StockCollectionValidation::class,
            'dataRules' => StockEntityValidation::class
        ],
        'version' => new Version(1, 3)
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
        ],
        'version' => new Version(1, 3)
    ],
    '/stockLocation' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(StockLocationCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'StockLocationCollection',
        'entityRoute' => '/stockLocation/:id',
        'validation' => [
            'filterRules' => LocationCollectionValidation::class,
            'dataRules' => LocationEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/stockLocation/:id' => [
        'controllers' => function($id) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Location::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($id, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'StockLocationEntity',
        'validation' => [
            "dataRules" => LocationEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => StockLocationMapper::class,
            'entityClass' => StockLocationEntity::class,
            'serviceClass' => StockLocationService::class
        ],
        'version' => new Version(1, 1)
    ],
    '/stockLog' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(StockLogCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'StockLogCollection',
        //'entityRoute' => '/stockLog/:stockId',
        'validation' => [
            'filterRules' => StockLogFilterValidation::class,
            //'dataRules' => StockLogEntityValidation::class
        ],
        'version' => new Version(1, 2)
    ],
    // Deliberately left out /stockLog/:id for now
];
