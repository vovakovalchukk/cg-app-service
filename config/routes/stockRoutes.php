<?php
use CG\Controllers\Stock\Stock;
use CG\Controllers\Stock\Stock\Collection as StockCollection;

use CG\InputValidation\Stock\Entity as StockEntityValidation;
use CG\InputValidation\Stock\Filter as StockCollectionValidation;

use CG\Stock\Entity as StockEntity;
use CG\Stock\Mapper as StockMapper;
use CG\Stock\Service as StockService;

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
    ]
];
 