<?php

use CG\Controllers\PurchaseOrder\PurchaseOrder\Collection as PurchaseOrderCollectionController;
use CG\Controllers\PurchaseOrder\PurchaseOrder as PurchaseOrderController;
use CG\InputValidation\PurchaseOrder\Entity as ValidationEntity;
use CG\InputValidation\PurchaseOrder\Filter as ValidationFilter;
use CG\PurchaseOrder\Entity;
use CG\PurchaseOrder\Mapper;
use CG\Slim\Versioning\Version;
use Slim\Slim;

return [
    "/purchaseOrder" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => ValidationFilter::class,
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
            "dataRules" => ValidationEntity::class,
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
            'mapperClass' => Mapper::class,
            'entityClass' => Entity::class
        ]
    ],
];
