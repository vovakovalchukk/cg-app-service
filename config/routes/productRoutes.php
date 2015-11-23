<?php
use CG\Controllers\Product\Product;
use CG\Controllers\Product\Product\Collection as ProductCollection;
use CG\InputValidation\Product\Entity as ProductEntityValidation;
use CG\InputValidation\Product\Filter as ProductCollectionValidation;
use CG\Product\Entity as ProductEntity;
use CG\Product\Mapper as ProductMapper;
use CG\Product\Service\Service as ProductService;

use CG\Controllers\Product\VariationAttributeMap\VariationAttributeMap as VariationAttributeMap;
use CG\Controllers\Product\VariationAttributeMap\VariationAttributeMap\Collection as VariationAttributeMapCollection;
use CG\InputValidation\Product\VariationAttributeMap\Entity as VariationAttributeMapEntityValidation;
use CG\InputValidation\Product\VariationAttributeMap\Filter as VariationAttributeMapFilterValidation;
use CG\Product\VariationAttributeMap\Mapper as VariationAttributeMapMapper;
use CG\Product\VariationAttributeMap\Entity as VariationAttributeMapEntity;
use CG\Product\VariationAttributeMap\Service as VariationAttributeMapService;

use CG\Controllers\ProductDetail\ProductDetail as ProductDetailController;
use CG\Controllers\ProductDetail\ProductDetail\Collection as ProductDetailCollectionController;
use CG\InputValidation\ProductDetail\Entity as ProductDetailEntityValidation;
use CG\InputValidation\ProductDetail\Filter as ProductDetailCollectionValidation;
use CG\Product\Detail\Entity as ProductDetailEntity;
use CG\Product\Detail\Mapper as ProductDetailMapper;
use CG\Product\Detail\RestService as ProductDetailService;

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
        'entityRoute' => '/product/:productId',
        'validation' => [
            'filterRules' => ProductCollectionValidation::class,
            'dataRules' => ProductEntityValidation::class
        ],
        'version' => new Version(1, 6)
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
        'version' => new Version(1, 6)
    ],
    '/product/:productId/variationAttributeMap' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(VariationAttributeMapCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'VariationAttributeMapCollection',
        'entityRoute' => '/product/:productId/variationAttributeMap/:variationAttributeMapId',
        'validation' => [
            'filterRules' => VariationAttributeMapFilterValidation::class,
            'dataRules' => VariationAttributeMapEntityValidation::class
        ]
    ],
    '/product/:productId/variationAttributeMap/:variationAttributeMapId' => [
        'controllers' => function($productId, $variationAttributeMapId) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(VariationAttributeMap::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productId, $variationAttributeMapId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'VariationAttributeMapEntity',
        'validation' => [
            'dataRules' => VariationAttributeMapEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => VariationAttributeMapMapper::class,
            'entityClass' => VariationAttributeMapEntity::class,
            'serviceClass' => VariationAttributeMapService::class
        ]
    ],
    '/productDetail' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ProductDetailCollectionController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ProductDetailCollection',
        'entityRoute' => '/productDetail/:productDetailId',
        'validation' => [
            'filterRules' => ProductDetailCollectionValidation::class,
            'dataRules' => ProductDetailEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productDetail/:productDetailId' => [
        'controllers' => function($productDetailId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ProductDetailController::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($productDetailId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ProductDetailEntity',
        'validation' => [
            "dataRules" => ProductDetailEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductDetailMapper::class,
            'entityClass' => ProductDetailEntity::class,
            'serviceClass' => ProductDetailService::class
        ],
        'version' => new Version(1, 1)
    ],
];
