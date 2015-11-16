<?php
use CG\Controllers\OrderCounts\Entity as OrderCounts;
use CG\Controllers\OrderCounts\Collection as OrderCountsCollection;

use CG\InputValidation\OrderCounts\Entity as OrderCountsEntityValidation;
use CG\InputValidation\OrderCounts\Filter as OrderCountsCollectionValidation;

use CG\Location\Entity as LocationEntity;
use CG\Location\Mapper as LocationMapper;
use CG\Location\Service as LocationService;

use CG\Slim\Versioning\Version;

return [
    '/location' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(OrderCountsCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'OrderCollection',
        'entityRoute' => '/orderCounts/:OUId',
        'validation' => [
            'filterRules' => OrderCountsCollectionValidation::class,
            'dataRules' => OrderCountsEntityValidation::class
        ]
    ],
    '/orderCounts/:OUId' => [
        'controllers' => function($locationId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(OrderCounts::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($locationId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderEntity',
        'validation' => [
            "dataRules" => OrderCountsEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => LocationMapper::class,
            'entityClass' => LocationEntity::class,
            'serviceClass' => LocationService::class
        ]
    ]
];