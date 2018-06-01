<?php

use CG\Controllers\Category\Collection as CollectionController;
use CG\Controllers\Category\Entity as CategoryController;
use CG\Controllers\CategoryExternal\Collection as CategoryExternalCollectionController;
use CG\Controllers\CategoryExternal\Entity as CategoryExternalController;
use CG\Controllers\CategoryTemplate\Collection as CategoryTemplateCollectionController;
use CG\Controllers\CategoryTemplate\Entity as CategoryTemplateController;
use CG\Controllers\CategoryVersionMap\Entity as CategoryVersionMapController;
use CG\InputValidation\Category\Entity as CategoryValidation;
use CG\InputValidation\Category\Filter as FilterValidation;
use CG\InputValidation\CategoryExternal\Entity as CategoryExternalValidation;
use CG\InputValidation\CategoryExternal\Filter as CategoryExternalFilterValidation;
use CG\InputValidation\CategoryTemplate\Entity as CategoryTemplateValidation;
use CG\InputValidation\CategoryTemplate\Filter as FilterTemplateValidation;
use CG\Product\Category\Entity as Category;
use CG\Product\Category\ExternalData\Entity as CategoryExternal;
use CG\Product\Category\ExternalData\Mapper as CategoryExternalMapper;
use CG\Product\Category\ExternalData\Service as CategoryExternalService;
use CG\Product\Category\Mapper as CategoryMapper;
use CG\Product\Category\Service as CategoryService;
use CG\Product\Category\Template\Entity as CategoryTemplate;
use CG\Product\Category\Template\Mapper as CategoryTemplateMapper;
use CG\Product\Category\Template\Service as CategoryTemplateService;
use CG\Product\Category\VersionMap\Entity as CategoryVersionMap;
use CG\Product\Category\VersionMap\Mapper as CategoryVersionMapMapper;
use CG\Product\Category\VersionMap\Service as CategoryVersionMapService;
use CG\InputValidation\CategoryVersionMap\Entity as CategoryVersionMapValidation;
use CG\InputValidation\CategoryVersionMap\Filter as CategoryVersionMapFilterValidation;
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
    '/categoryTemplate' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryTemplateCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'CategoryTemplateCollection',
        'entityRoute' => '/categoryTemplate/:id',
        'validation' => [
            'filterRules' => FilterTemplateValidation::class,
            'dataRules' => CategoryTemplateValidation::class
        ],
        'version' => new Version(1, 2)
    ],
    '/categoryTemplate/:id' => [
        'controllers' => function($categoryTemplateId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryTemplateController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($categoryTemplateId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'CategoryTemplateEntity',
        'validation' => [
            'dataRules' => CategoryTemplateValidation::class,
        ],
        'version' => new Version(1, 2),
        'eTag' => [
            'mapperClass' => CategoryTemplateMapper::class,
            'entityClass' => CategoryTemplate::class,
            'serviceClass' => CategoryTemplateService::class
        ]
    ],
    '/categoryVersionMap/:id' => [
        'controllers' => function($categoryVersionMapId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CategoryVersionMapController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($categoryVersionMapId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'CategoryVersionMapEntity',
        'validation' => [
            'dataRules' => CategoryVersionMapValidation::class
        ],
        'version' => new Version(1, 1),
        'eTag' => array(
            'mapperClass' => CategoryVersionMapMapper::class,
            'entityClass' => CategoryVersionMap::class,
            'serviceClass' => CategoryVersionMapService::class
        )
    ],
];
