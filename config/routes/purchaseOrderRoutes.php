<?php

use CG\Controllers\PurchaseOrder\PurchaseOrder\Collection as PurchaseOrderCollectionController;
use CG\Controllers\PurchaseOrder\PurchaseOrder as PurchaseOrderController;
use CG\Controllers\PurchaseOrder\PurchaseOrderItem\Collection as PurchaseOrderItemCollectionController;
use CG\Controllers\PurchaseOrder\PurchaseOrderItem as PurchaseOrderItemController;
use CG\InputValidation\PurchaseOrder\Entity as PurchaseOrderValidationEntity;
use CG\InputValidation\PurchaseOrder\Filter as PurchaseOrderValidationFilter;
use CG\InputValidation\PurchaseOrderItem\Entity as PurchaseOrderItemValidationEntity;
use CG\InputValidation\PurchaseOrderItem\Filter as PurchaseOrderItemValidationFilter;
use CG\PurchaseOrder\Entity as PurchaseOrderEntity;
use CG\PurchaseOrder\Mapper as PurchaseOrderMapper;
use CG\PurchaseOrder\Service as PurchaseOrderService;
use CG\PurchaseOrder\PurchaseOrderItem\Service as PurchaseOrderItemService;
use CG\PurchaseOrder\PurchaseOrderItem\Entity as PurchaseOrderItemEntity;
use CG\PurchaseOrder\PurchaseOrderItem\Mapper as PurchaseOrderItemMapper;
use CG\Slim\Versioning\Version;
use Slim\Slim;

return [
    "/purchaseOrderItem" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => PurchaseOrderItemValidationEntity::class,
            "filterRules" => PurchaseOrderItemValidationFilter::class,
        ],
        "controllers" => function() use ($serviceManager) {

            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PurchaseOrderItemCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/purchaseOrderItem/:id',
        "name" => "PurchaseOrderItemCollection",
        "version" => new Version(1, 1)
    ],
    "/purchaseOrderItem/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => PurchaseOrderItemValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($serviceManager) {

            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PurchaseOrderItemController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "PurchaseOrderItem",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => PurchaseOrderItemMapper::class,
            'entityClass' => PurchaseOrderItemEntity::class,
            'serviceClass' => PurchaseOrderItemService::class
        ]
    ],
    "/purchaseOrder" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => PurchaseOrderValidationEntity::class,
            "filterRules" => PurchaseOrderValidationFilter::class,
        ],
        "controllers" => function() use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PurchaseOrderCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/purchaseOrder/:id',
        "name" => "PurchaseOrderCollection",
        "version" => new Version(1, 1)
    ],
    "/purchaseOrder/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => PurchaseOrderValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($serviceManager) {

            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(PurchaseOrderController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "PurchaseOrder",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => PurchaseOrderMapper::class,
            'entityClass' => PurchaseOrderEntity::class,
            'serviceClass' => PurchaseOrderService::class
        ]
    ]

];
