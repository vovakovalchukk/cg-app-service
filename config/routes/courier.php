<?php
use CG\Amazon\ShippingService\Entity as AmazonEntity;
use CG\Amazon\ShippingService\Mapper as AmazonMapper;
use CG\Amazon\ShippingService\Service\Service as AmazonService;
use CG\Amazon\ShippingService\Storage\Api as Amazon;
use CG\Controllers\Courier\Amazon\Collection as AmazonCollectionController;
use CG\Controllers\Courier\Amazon\Entity as AmazonEntityController;
use CG\Controllers\Root;
use CG\Courier\Mapper;
use CG\InputValidation\Amazon\Carrier\Entity as AmazonDataRules;
use CG\InputValidation\Amazon\Carrier\Filter as AmazonFilterRules;
use CG\Slim\Versioning\Version;
use Slim\Slim;
use Zend\Di\Di;

/** @var Slim $app */
/** @var Di $di */
return [
    '/courier' => [
        'controllers' => function() use ($app, $di) {
                $controller = $di->newInstance(Root::class, ['mapper' => Mapper::class]);
                $method = $app->request()->getMethod();
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'Courier',
    ],
    Amazon::URI => [
        'controllers' => function() use ($app, $di) {
                $controller = $di->newInstance(AmazonCollectionController::class);
                $method = $app->request()->getMethod();
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'AmazonCourierCollection',
        'validation' => [
            'filterRules' => AmazonFilterRules::class,
            'dataRules' => AmazonDataRules::class,
        ],
        'entityRoute' => Amazon::URI . '/:serviceId',
        'version' => new Version(1, 1),
    ],
    Amazon::URI . '/:serviceId' => [
        'controllers' => function($serviceId) use ($app, $di) {
                $controller = $di->newInstance(AmazonEntityController::class);
                $method = $app->request()->getMethod();
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($serviceId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'AmazonCourierEntity',
        'validation' => [
            'dataRules' => AmazonDataRules::class,
        ],
        'eTag' => [
            'mapperClass' => AmazonMapper::class,
            'entityClass' => AmazonEntity::class,
            'serviceClass' => AmazonService::class
        ],
        'version' => new Version(1, 1),
    ],
];