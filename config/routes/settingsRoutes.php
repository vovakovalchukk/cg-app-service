<?php
use Slim\Slim;
use CG\Slim\Versioning\Version;
use CG\Controllers\Settings;
use CG\Controllers\Settings\Invoice;
use CG\Controllers\Settings\Invoice\Collection as InvoiceCollection;

use CG\InputValidation\Settings\Invoice\Filter as CollectionValidation;
use CG\InputValidation\Settings\Invoice\Entity as EntityValidation;

use CG\Controllers\Settings\Shipping\Alias as Alias;
use CG\Controllers\Settings\Shipping\Alias\Collection as AliasCollection;

use CG\InputValidation\Settings\Alias\Filter as AliasCollectionValidation;
use CG\InputValidation\Settings\Alias\Entity as AliasEntityValidation;

use CG\Settings\Invoice\Shared\Entity as InvoiceEntity;
use CG\Settings\Invoice\Shared\Mapper as InvoiceMapper;
use CG\Settings\Invoice\Service\Service as InvoiceService;

use CG\Settings\Shipping\Alias\Entity as AliasEntity;
use CG\Settings\Shipping\Alias\Mapper as AliasMapper;
use CG\Settings\Shipping\Alias\Service as AliasService;

use CG\Controllers\Settings\PickList;
use CG\Controllers\Settings\PickList\Collection as PickListCollection;

use CG\InputValidation\Settings\PickList\Filter as PickListCollectionValidation;
use CG\InputValidation\Settings\PickList\Entity as PickListEntityValidation;

use CG\Settings\PickList\Entity as PickListEntity;
use CG\Settings\PickList\Mapper as PickListMapper;
use CG\Settings\PickList\Service as PickListService;

use CG\Controllers\Settings\Api;
use CG\Controllers\Settings\Api\Collection as ApiCollection;

use CG\InputValidation\Settings\Api\Filter as ApiCollectionValidation;
use CG\InputValidation\Settings\Api\Entity as ApiEntityValidation;

use CG\Settings\Api\Entity as ApiEntity;
use CG\Settings\Api\Mapper as ApiMapper;
use CG\Settings\Api\Service as ApiService;

use CG\Controllers\Settings\Product;
use CG\Controllers\Settings\Product\Collection as ProductCollection;

use CG\InputValidation\Settings\Product\Filter as ProductCollectionValidation;
use CG\InputValidation\Settings\Product\Entity as ProductEntityValidation;

use CG\Settings\Product\Entity as ProductEntity;
use CG\Settings\Product\Mapper as ProductMapper;
use CG\Settings\Product\Service as ProductService;

use CG\Controllers\Settings\SetupProgress;
use CG\Controllers\Settings\SetupProgress\Collection as SetupProgressCollection;
use CG\InputValidation\Settings\SetupProgress\Filter as SetupProgressCollectionValidation;
use CG\InputValidation\Settings\SetupProgress\Entity as SetupProgressEntityValidation;
use CG\Settings\SetupProgress\Entity as SetupProgressEntity;
use CG\Settings\SetupProgress\Mapper as SetupProgressMapper;
use CG\Settings\SetupProgress\RestService as SetupProgressService;

return [
    '/settings' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Settings::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'Settings'
    ],
    '/settings/invoice' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(InvoiceCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'InvoiceSettingsCollection',
        'entityRoute' => '/settings/invoice/:id',
        'validation' => [
            'filterRules' => CollectionValidation::class
        ],
        'version' => new Version(1, 3),
    ],
    '/settings/invoice/:id' => [
        'controllers' => function($invoiceId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Invoice::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($invoiceId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'InvoiceSettings',
        'validation' => [
            "dataRules" => EntityValidation::class,
        ],
        'version' => new Version(1, 3),
        'eTag' => [
            'mapperClass' => InvoiceMapper::class,
            'entityClass' => InvoiceEntity::class,
            'serviceClass' => InvoiceService::class
        ]
    ],
    '/settings/shipping' => [
        'via' => ['GET'],
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();
                $controller = $di->get(Settings\Shipping::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
        },
        'name' => "ShippingSettings"
    ],
    '/settings/shipping/alias' => [
        'controllers' => function() use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(AliasCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'AliasSettingsCollection',
        'validation' => [
            'filterRules' => AliasCollectionValidation::class,
            'dataRules' => AliasEntityValidation::class
        ],
        'entityRoute' => '/settings/shipping/alias/:aliasId',
        'version' => new Version(1, 3),
    ],
    '/settings/shipping/alias/:aliasId' => [
        'controllers' => function($aliasId) use ($di, $app) {
                $method = $app->request()->getMethod();

                $controller = $di->get(Alias::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($aliasId, $app->request()->getBody())
                );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'AliasSettingsEntity',
        'validation' => [
            "dataRules" => AliasEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => AliasMapper::class,
            'entityClass' => AliasEntity::class,
            'serviceClass' => AliasService::class
        ],
        'version' => new Version(1, 3),
    ],
    '/settings/pickList' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PickListCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'PickListSettingsCollection',
        'entityRoute' => '/settings/pickList/:id',
        'validation' => [
            'filterRules' => PickListCollectionValidation::class
        ]
    ],
    '/settings/pickList/:id' => [
        'controllers' => function($pickListId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PickList::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($pickListId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'PickListSettings',
        'validation' => [
            'dataRules' => PickListEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => PickListMapper::class,
            'entityClass' => PickListEntity::class,
            'serviceClass' => PickListService::class
        ]
    ],
    '/settings/api' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(ApiCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ApiSettingsCollection',
        'entityRoute' => '/settings/api/:id',
        'validation' => [
            'filterRules' => ApiCollectionValidation::class
        ]
    ],
    '/settings/api/:id' => [
        'controllers' => function($ouId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Api::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($ouId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ApiSettingsEntity',
        'validation' => [
            'dataRules' => ApiEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => ApiMapper::class,
            'entityClass' => ApiEntity::class,
            'serviceClass' => ApiService::class
        ]
    ],
    '/settings/product' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(ProductCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ProductSettingsCollection',
        'entityRoute' => '/settings/product/:id',
        'validation' => [
            'filterRules' => ProductCollectionValidation::class
        ]
    ],
    '/settings/product/:id' => [
        'controllers' => function($ouId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Product::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($ouId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ProductSettingsEntity',
        'validation' => [
            'dataRules' => ProductEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => ProductMapper::class,
            'entityClass' => ProductEntity::class,
            'serviceClass' => ProductService::class
        ]
    ],
    '/settings/setupProgress' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(SetupProgressCollection::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'SetupProgressSettingsCollection',
        'entityRoute' => '/settings/setupProgress/:id',
        'validation' => [
            'filterRules' => SetupProgressCollectionValidation::class
        ]
    ],
    '/settings/setupProgress/:id' => [
        'controllers' => function($ouId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(SetupProgress::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($ouId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'SetupProgressSettingsEntity',
        'validation' => [
            'dataRules' => SetupProgressEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => SetupProgressMapper::class,
            'entityClass' => SetupProgressEntity::class,
            'serviceClass' => SetupProgressService::class
        ]
    ],
];
