<?php
use CG\Controllers\ExchangeRate\Entity as ExchangeRate;
use CG\Controllers\ExchangeRate\Collection as ExchangeRateCollection;
use CG\InputValidation\ExchangeRate\Entity as ExchangeRateEntityValidation;
use CG\InputValidation\ExchangeRate\Filter as ExchangeRateCollectionValidation;
use CG\Currency\Shared\ExchangeRate\Entity as ExchangeRateEntity;
use CG\Currency\Shared\ExchangeRate\Mapper as ExchangeRateMapper;
use CG\Currency\Shared\ExchangeRate\Service as ExchangeRateService;


return [
    '/exchangeRate' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ExchangeRateCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ExchangeRateCollection',
        'entityRoute' => '/exchangeRate/:date',
        'validation' => [
            'filterRules' => ExchangeRateCollectionValidation::class,
            'dataRules' => ExchangeRateEntityValidation::class
        ]
    ],
    '/exchangeRate/:date' => [
        'controllers' => function($date) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ExchangeRate::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($date, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'ExchangeRateEntity',
        'validation' => [
            "dataRules" => ExchangeRateEntityValidation::class,
        ],
        'eTag' => [
            'mapperClass' => ExchangeRateMapper::class,
            'entityClass' => ExchangeRateEntity::class,
            'serviceClass' => ExchangeRateService::class
        ]
    ]
];