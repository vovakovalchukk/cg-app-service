<?php

use CG\Controllers\Category\Collection as CollectionController;
use CG\Controllers\Category\Entity as CategoryController;
use CG\Controllers\CategoryExternal\Collection as CategoryExternalCollectionController;
use CG\Controllers\CategoryExternal\Entity as CategoryExternalController;
use CG\InputValidation\Category\Entity as CategoryValidation;
use CG\InputValidation\Category\Filter as FilterValidation;
use CG\InputValidation\CategoryExternal\Entity as CategoryExternalValidation;
use CG\InputValidation\CategoryExternal\Filter as CategoryExternalFilterValidation;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;
use CG\Product\Category\ExternalData\Mapper as CategoryExternalMapper;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\Mapper as CategoryMapper;
use CG\Product\Category\Service as CategoryService;
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
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'CategoryCollection',
        'entityRoute' => '/category/:categoryId',
        'validation' => [
            'filterRules' => FilterValidation::class,
            'dataRules' => CategoryValidation::class
        ],
        'version' => new Version(1, 2)
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
        'version' => new Version(1, 2),
        'eTag' => [
            'mapperClass' => CategoryMapper::class,
            'entityClass' => Category::class,
            'serviceClass' => CategoryService::class
        ]
    ],
    '/categoryExternal' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryExternalCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'CategoryExternalCollection',
        'entityRoute' => '/categoryExternal/:categoryId',
        'validation' => [
            'filterRules' => CategoryExternalFilterValidation::class,
            'dataRules' => CategoryExternalValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/categoryExternal/:categoryId' => [
        'controllers' => function($productLinkNodeId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryExternalController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkNodeId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'CategoryExternalEntity',
        'validation' => [
            'dataRules' => CategoryExternalValidation::class,
        ],
        'version' => new Version(1, 1),
        'eTag' => [
            'mapperClass' => CategoryExternalMapper::class,
            'entityClass' => CategoryExternal::class,
            'serviceClass' => CategoryExternalService::class
        ]
    ],
];
