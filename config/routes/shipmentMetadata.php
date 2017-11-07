<?php

use CG\Controllers\ShipmentMetadata\ShipmentMetadata\Collection as ShipmentMetadataCollectionController;
use CG\Controllers\ShipmentMetadata\ShipmentMetadata as ShipmentMetadataController;
use CG\InputValidation\ShipmentMetadata\Entity as ValidationEntity;
use CG\InputValidation\ShipmentMetadata\Filter as ValidationFilter;
use CG\Order\Service\ShipmentMetadata\RestService;
use CG\Order\Shared\ShipmentMetadata\Entity;
use CG\Order\Shared\ShipmentMetadata\Mapper;
use CG\Slim\Versioning\Version;

return [
    "/shipmentMetadata" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => ValidationFilter::class,
        ],
        "controllers" => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(ShipmentMetadataCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/shipmentMetadata/:id',
        "name" => "ShipmentMetadataCollection",
        "version" => new Version(1, 1)
    ],
    "/shipmentMetadata/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(ShipmentMetadataController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "ShipmentMetadataEntity",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => Mapper::class,
            'entityClass' => Entity::class,
            'serviceClass' => RestService::class
        ]
    ],
];