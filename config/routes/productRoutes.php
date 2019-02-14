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

use CG\Controllers\ProductChannelDetail\ProductChannelDetail as ProductChannelDetailController;
use CG\Controllers\ProductChannelDetail\ProductChannelDetail\Collection as ProductChannelDetailCollectionController;
use CG\InputValidation\ProductChannelDetail\Entity as ProductChannelDetailEntityValidation;
use CG\InputValidation\ProductChannelDetail\Filter as ProductChannelDetailCollectionValidation;
use CG\Product\ChannelDetail\Entity as ProductChannelDetailEntity;
use CG\Product\ChannelDetail\Mapper as ProductChannelDetailMapper;
use CG\Product\ChannelDetail\Service as ProductChannelDetailService;

use CG\Controllers\ProductAccountDetail\ProductAccountDetail as ProductAccountDetailController;
use CG\Controllers\ProductAccountDetail\ProductAccountDetail\Collection as ProductAccountDetailCollectionController;
use CG\InputValidation\ProductAccountDetail\Entity as ProductAccountDetailEntityValidation;
use CG\InputValidation\ProductAccountDetail\Filter as ProductAccountDetailCollectionValidation;
use CG\Product\AccountDetail\Entity as ProductAccountDetailEntity;
use CG\Product\AccountDetail\Mapper as ProductAccountDetailMapper;
use CG\Product\AccountDetail\Service as ProductAccountDetailService;

use CG\Controllers\ProductCategoryDetail\ProductCategoryDetail as ProductCategoryDetailController;
use CG\Controllers\ProductCategoryDetail\ProductCategoryDetail\Collection as ProductCategoryDetailCollectionController;
use CG\InputValidation\ProductCategoryDetail\Entity as ProductCategoryDetailEntityValidation;
use CG\InputValidation\ProductCategoryDetail\Filter as ProductCategoryDetailCollectionValidation;
use CG\Product\CategoryDetail\Entity as ProductCategoryDetailEntity;
use CG\Product\CategoryDetail\Mapper as ProductCategoryDetailMapper;
use CG\Product\CategoryDetail\Service as ProductCategoryDetailService;

use CG\Controllers\ProductLink\Entity as ProductLinkController;
use CG\Controllers\ProductLink\Collection as ProductLinkCollectionController;
use CG\InputValidation\ProductLink\Entity as ProductLinkEntityValidation;
use CG\InputValidation\ProductLink\Filter as ProductLinkCollectionValidation;
use CG\Product\Link\Entity as ProductLinkEntity;
use CG\Product\Link\Mapper as ProductLinkMapper;
use CG\Product\Link\Service as ProductLinkService;

use CG\Controllers\ProductLinkLeaf\Entity as ProductLinkLeafController;
use CG\Controllers\ProductLinkLeaf\Collection as ProductLinkLeafCollectionController;
use CG\InputValidation\ProductLinkLeaf\Entity as ProductLinkLeafEntityValidation;
use CG\InputValidation\ProductLinkLeaf\Filter as ProductLinkLeafCollectionValidation;

use CG\Controllers\ProductLinkNode\Entity as ProductLinkNodeController;
use CG\Controllers\ProductLinkNode\Collection as ProductLinkNodeCollectionController;
use CG\InputValidation\ProductLinkNode\Entity as ProductLinkNodeEntityValidation;
use CG\InputValidation\ProductLinkNode\Filter as ProductLinkNodeCollectionValidation;

use CG\Controllers\ProductLinkRelated\Entity as ProductLinkRelatedController;
use CG\Controllers\ProductLinkRelated\Collection as ProductLinkRelatedCollectionController;
use CG\InputValidation\ProductLinkRelated\Entity as ProductLinkRelatedValidation;
use CG\InputValidation\ProductLinkRelated\Filter as ProductLinkRelatedCollectionValidation;

use CG\Controllers\ProductLinkPaths\Entity as ProductLinkPathsController;
use CG\Controllers\ProductLinkPaths\Collection as ProductLinkPathsCollectionController;
use CG\InputValidation\ProductLinkPaths\Entity as ProductLinkPathsValidation;
use CG\InputValidation\ProductLinkPaths\Filter as ProductLinkPathsCollectionValidation;

use CG\Controllers\ProductPickingLocation\Entity as ProductPickingLocationController;
use CG\Controllers\ProductPickingLocation\Collection as ProductPickingLocationCollectionController;
use CG\InputValidation\ProductPickingLocation\Entity as ProductPickingLocationEntityValidation;
use CG\InputValidation\ProductPickingLocation\Filter as ProductPickingLocationCollectionValidation;

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
        'version' => new Version(1, 11)
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
        'version' => new Version(1, 11)
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
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'ProductDetailCollection',
        'entityRoute' => '/productDetail/:productDetailId',
        'validation' => [
            'filterRules' => ProductDetailCollectionValidation::class,
            'dataRules' => ProductDetailEntityValidation::class
        ],
        'version' => new Version(1, 5)
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
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'ProductDetailEntity',
        'validation' => [
            "dataRules" => ProductDetailEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductDetailMapper::class,
            'entityClass' => ProductDetailEntity::class,
            'serviceClass' => ProductDetailService::class
        ],
        'version' => new Version(1, 5)
    ],
    '/productChannelDetail' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductChannelDetailCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'ProductChannelDetailCollection',
        'entityRoute' => '/productChannelDetail/:productChannelDetailId',
        'validation' => [
            'filterRules' => ProductChannelDetailCollectionValidation::class,
            'dataRules' => ProductChannelDetailEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productChannelDetail/:productChannelDetailId' => [
        'controllers' => function($productChannelDetailId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductChannelDetailController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productChannelDetailId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'ProductChannelDetailEntity',
        'validation' => [
            'dataRules' => ProductChannelDetailEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductChannelDetailMapper::class,
            'entityClass' => ProductChannelDetailEntity::class,
            'serviceClass' => ProductChannelDetailService::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productAccountDetail' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductAccountDetailCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'ProductAccountDetailCollection',
        'entityRoute' => '/productAccountDetail/:productAccountDetailId',
        'validation' => [
            'filterRules' => ProductAccountDetailCollectionValidation::class,
            'dataRules' => ProductAccountDetailEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productAccountDetail/:productAccountDetailId' => [
        'controllers' => function($productAccountDetailId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductAccountDetailController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productAccountDetailId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'ProductAccountDetailEntity',
        'validation' => [
            'dataRules' => ProductAccountDetailEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductAccountDetailMapper::class,
            'entityClass' => ProductAccountDetailEntity::class,
            'serviceClass' => ProductAccountDetailService::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productCategoryDetail' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductCategoryDetailCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'PATCH', 'OPTIONS'],
        'name' => 'ProductCategoryDetailCollection',
        'entityRoute' => '/productCategoryDetail/:productCategoryDetailId',
        'validation' => [
            'filterRules' => ProductCategoryDetailCollectionValidation::class,
            'dataRules' => ProductCategoryDetailEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productCategoryDetail/:productCategoryDetailId' => [
        'controllers' => function($productCategoryDetailId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductCategoryDetailController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productCategoryDetailId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
        'name' => 'ProductCategoryDetailEntity',
        'validation' => [
            'dataRules' => ProductCategoryDetailEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductCategoryDetailMapper::class,
            'entityClass' => ProductCategoryDetailEntity::class,
            'serviceClass' => ProductCategoryDetailService::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLink' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductLinkCollection',
        'entityRoute' => '/productLink/:productLinkId',
        'validation' => [
            'filterRules' => ProductLinkCollectionValidation::class,
            'dataRules' => ProductLinkEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLink/:productLinkId' => [
        'controllers' => function($productLinkId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ProductLinkEntity',
        'validation' => [
            'dataRules' => ProductLinkEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ProductLinkMapper::class,
            'entityClass' => ProductLinkEntity::class,
            'serviceClass' => ProductLinkService::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLinkLeaf' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkLeafCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductLinkLeafCollection',
        'entityRoute' => '/productLinkLeaf/:productLinkLeafId',
        'validation' => [
            'filterRules' => ProductLinkLeafCollectionValidation::class,
            'dataRules' => ProductLinkLeafEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLinkLeaf/:productLinkLeafId' => [
        'controllers' => function($productLinkLeafId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkLeafController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkLeafId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'DELETE', 'OPTIONS'],
        'name' => 'ProductLinkLeafEntity',
        'validation' => [
            'dataRules' => ProductLinkLeafEntityValidation::class,
        ],
        'eTag' => false,
        'version' => new Version(1, 1)
    ],
    '/productLinkNode' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkNodeCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductLinkNodeCollection',
        'entityRoute' => '/productLinkNode/:productLinkNodeId',
        'validation' => [
            'filterRules' => ProductLinkNodeCollectionValidation::class,
            'dataRules' => ProductLinkNodeEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLinkNode/:productLinkNodeId' => [
        'controllers' => function($productLinkNodeId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkNodeController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkNodeId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'DELETE', 'OPTIONS'],
        'name' => 'ProductLinkNodeEntity',
        'validation' => [
            'dataRules' => ProductLinkNodeEntityValidation::class,
        ],
        'eTag' => false,
        'version' => new Version(1, 1)
    ],
    '/productLinkRelated' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkRelatedCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductLinkRelatedCollection',
        'entityRoute' => '/productLinkRelated/:productLinkRelatedOuIdSku',
        'validation' => [
            'filterRules' => ProductLinkRelatedCollectionValidation::class,
            'dataRules' => ProductLinkRelatedValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLinkRelated/:productLinkRelatedOuIdSku' => [
        'controllers' => function($productLinkRelatedOuIdSku) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkRelatedController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkRelatedOuIdSku, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'DELETE', 'OPTIONS'],
        'name' => 'ProductLinkRelatedEntity',
        'validation' => [
            'dataRules' => ProductLinkRelatedValidation::class,
        ],
        'eTag' => false,
        'version' => new Version(1, 1)
    ],
    '/productLinkPaths' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkPathsCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductLinkPathsCollection',
        'entityRoute' => '/productLinkPaths/:productLinkPathsId',
        'validation' => [
            'filterRules' => ProductLinkPathsCollectionValidation::class,
            'dataRules' => ProductLinkPathsValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productLinkPaths/:productLinkPathsId' => [
        'controllers' => function($productLinkPathsId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ProductLinkPathsController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productLinkPathsId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'DELETE', 'OPTIONS'],
        'name' => 'ProductLinkPathsEntity',
        'validation' => [
            'dataRules' => ProductLinkPathsValidation::class,
        ],
        'eTag' => false,
        'version' => new Version(1, 1)
    ],
    '/productPickingLocation' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(ProductPickingLocationCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductPickingLocationCollection',
        'entityRoute' => '/productPickingLocation/:productPickingLocationId',
        'validation' => [
            'filterRules' => ProductPickingLocationCollectionValidation::class,
            'dataRules' => ProductPickingLocationEntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/productPickingLocation/:productPickingLocationId' => [
        'controllers' => function($productPickingLocationId) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(ProductPickingLocationController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($productPickingLocationId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductPickingLocationEntity',
        'validation' => [
            'dataRules' => ProductPickingLocationEntityValidation::class,
        ],
        'eTag' => false,
        'version' => new Version(1, 1)
    ],
];
