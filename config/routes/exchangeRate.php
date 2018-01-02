<?php
use CG\Controllers\ExchangeRate\Collection as ExchangeRateCollectionController;
use CG\Controllers\ExchangeRate\Entity as ExchangeRateController;
use CG\InputValidation\ExchangeRate\Entity as ExchangeRateEntityValidation;
use CG\InputValidation\ExchangeRate\Filter as ExchangeRateCollectionValidation;
use CG\ExchangeRate\Entity as ExchangeRate;
use CG\ExchangeRate\Mapper as ExchangeRateMapper;
use CG\ExchangeRate\Service as ExchangeRateService;

return [
    '/exchangeRate' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ExchangeRateCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ExchangeRateCollection',
        'entityRoute' => '/exchangeRate/:exchangeRateId',
        'validation' => [
            'filterRules' => ExchangeRateCollectionValidation::class,
            'dataRules' => ExchangeRateEntityValidation::class
        ]
    ],
    '/exchangeRate/:exchangeRateId' => [
        'controllers' => function($exchangeRateId) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(ExchangeRateController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($exchangeRateId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ExchangeRateEntity',
        'validation' => [
            "dataRules" => ExchangeRateEntityValidation::class
        ],
        'version' => new Version(1, 1),
        'eTag' => [
            'mapperClass' => ExchangeRateMapper::class,
            'entityClass' => ExchangeRate::class,
            'serviceClass' => ExchangeRateService::class
        ],
    ]
];