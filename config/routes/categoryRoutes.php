<?php

use CG\Controllers\Category\Collection as CollectionController;
use CG\Controllers\Category\Entity as CategoryController;
use CG\InputValidation\Category\Entity as CategoryValidation;
use CG\InputValidation\Category\Filter as FilterValidation;
use CG\Product\Category\Entity;
use CG\Product\Category\Mapper;
use CG\Product\Category\Service;
use CG\Slim\Versioning\Version;

return [
    '/category' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'CategoryCollection',
        'entityRoute' => '/category/:categoryId',
        'validation' => [
            'filterRules' => FilterValidation::class,
            'dataRules' => CategoryValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/category/:categoryId' => [
        'controllers' => function($productLinkNodeId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkNodeId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'CategoryEntity',
        'validation' => [
            'dataRules' => CategoryValidation::class,
        ],
        'version' => new Version(1, 1),
        'eTag' => [
            'mapperClass' => Mapper::class,
            'entityClass' => Entity::class,
            'serviceClass' => Service::class
        ]
    ],
];
