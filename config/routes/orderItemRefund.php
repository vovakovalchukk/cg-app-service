<?php

use CG\Controllers\Order\Item\Refund\Collection as CollectionController;
use CG\Controllers\Order\Item\Refund\Entity as EntityController;
use CG\InputValidation\Order\Item\Refund\Entity as EntityValidation;
use CG\InputValidation\Order\Item\Refund\Filter as FilterValidation;
use CG\Order\Shared\Item\Refund\Entity as OrderItemRefund;
use CG\Order\Shared\Item\Refund\Mapper as OrderItemRefundMapper;
use CG\Order\Service\Item\Refund\RestService as OrderItemRefundService;
use CG\Slim\Versioning\Version;

return [
    '/orderItemRefund' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'OrderItemRefundCollection',
        'entityRoute' => '/orderItemRefund/:orderItemRefundId',
        'validation' => [
            'filterRules' => FilterValidation::class,
            'dataRules' => EntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/orderItemRefund/:orderItemRefundId' => [
        'controllers' => function($orderItemRefundId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(EntityController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($orderItemRefundId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderItemRefundEntity',
        'validation' => [
            'dataRules' => EntityValidation::class,
        ],
        'version' => new Version(1, 1),
        'eTag' => [
            'mapperClass' => OrderItemRefundMapper::class,
            'entityClass' => OrderItemRefund::class,
            'serviceClass' => OrderItemRefundService::class
        ]
    ],
];
