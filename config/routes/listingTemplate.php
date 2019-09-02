<?php

use CG\Controllers\ListingTemplate\Collection as ListingTemplateCollectionController;
use CG\Controllers\ListingTemplate\Entity as ListingTemplateController;
use CG\InputValidation\ListingTemplate\Entity as ValidationEntity;
use CG\InputValidation\ListingTemplate\Filter as ValidationFilter;
use CG\Listing\Template\RestService;
use CG\Listing\Template\Entity;
use CG\Listing\Template\Mapper;
use CG\Slim\Versioning\Version;
use Slim\Slim;

return [
    "/listingTemplate" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => ValidationFilter::class,
        ],
        "controllers" => function() use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(ListingTemplateCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/listingTemplate/:id',
        "name" => "ListingTemplateCollection",
        "version" => new Version(1, 1)
    ],
    "/listingTemplate/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($serviceManager) {
            $di = $serviceManager->get('Di');
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(ListingTemplateController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "ListingTemplateEntity",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => Mapper::class,
            'entityClass' => Entity::class,
            'serviceClass' => RestService::class
        ]
    ],
];