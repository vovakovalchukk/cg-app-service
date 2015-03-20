<?php
use CG\Controllers\Product\Product;
use CG\Controllers\Product\Product\Collection as ProductCollection;

use CG\InputValidation\Product\Entity as ProductEntityValidation;
use CG\InputValidation\Product\Filter as ProductCollectionValidation;

use CG\Product\Entity as ProductEntity;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Service\Service as ProductService;

use CG\Controllers\Product\VariationMap\VariationMap as VariationMap;
use CG\Controllers\Product\VariationMap\VariationMap\Collection as VariationMapCollection;

use CG\InputValidation\Product\VariationMap\Entity as VariationMapEntityValidation;
use CG\InputValidation\Product\VariationMap\Filter as VariationMapFilterValidation;

use CG\Product\VariationMap\Mapper as VariationMapMapper;
use CG\Product\VariationMap\Entity as VariationMapEntity;
use CG\Product\VariationMap\Service as VariationMapService;


use CG\Slim\Versioning\Version;

return [
    '/product' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ProductCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ProductCollection',
        'validation' => [
            'filterRules' => ProductCollectionValidation::class,
            'dataRules' => ProductEntityValidation::class
        ],
        'version' => new Version(1, 4)
    ],
    '/product/:productId' => [
        'controllers' => function($productId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Product::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($productId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ProductEntity',
        'validation' => [
            "dataRules" => ProductEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductMapper::class,
            'entityClass' => ProductEntity::class,
            'serviceClass' => ProductService::class
        ],
        'version' => new Version(1, 4)
    ],
    '/product/:productId/variationMap' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(VariationMapCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'VariationMapCollectionCollection',
        'validation' => [
            'filterRules' => VariationMapFilterValidation::class,
            'dataRules' => VariationMapEntityValidation::class
        ]
    ],
    '/product/:productId/variationMap/:variationMapId' => [
        'controllers' => function($productId, $variationMapId) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(VariationMap::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productId, $variationMapId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'VariationMapEntity',
        'validation' => [
            'dataRules' => VariationMapEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => VariationMapMapper::class,
            'entityClass' => VariationMapEntity::class,
            'serviceClass' => VariationMapService::class
        ]
    ]
];
 