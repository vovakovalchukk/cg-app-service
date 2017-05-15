<?php

use CG\Controllers\OrderLink\OrderLink\Collection as OrderLinkCollectionController;
use CG\Controllers\OrderLink\OrderLink as OrderLinkController;
use CG\InputValidation\OrderLink\Entity as ValidationEntity;
use CG\InputValidation\OrderLink\Filter as ValidationFilter;
use CG\Order\Service\OrderLink\RestService;
use CG\Order\Shared\OrderLink\Entity;
use CG\Order\Shared\OrderLink\Mapper;
use CG\Slim\Versioning\Version;
use Slim\Slim;

return [
    "/orderLink" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => ValidationFilter::class,
        ],
        "controllers" => function() use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(OrderLinkCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/orderLink/:id',
        "name" => "OrderLinkCollection",
        "version" => new Version(1, 1)
    ],
    "/orderLink/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(OrderLinkController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "ShippingMetadata",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => Mapper::class,
            'entityClass' => Entity::class,
            'serviceClass' => RestService::class
        ]
    ],
];