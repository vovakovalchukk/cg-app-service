<?php
use CG\Controllers\ExchangeRate\Collection as ExchangeRateCollection;
use CG\InputValidation\ExchangeRate\Entity as ExchangeRateEntityValidation;
use CG\InputValidation\ExchangeRate\Filter as ExchangeRateCollectionValidation;

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
        'validation' => [
            'filterRules' => ExchangeRateCollectionValidation::class,
            'dataRules' => ExchangeRateEntityValidation::class
        ]
    ],
    '/exchangeRate/:datetime' => [
        'controllers' => function($datetime) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ExchangeRateCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($datetime, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'ExchangeRateCollectionByDatetime',
        'validation' => [
            'filterRules' => ExchangeRateCollectionValidation::class,
            'dataRules' => ExchangeRateEntityValidation::class
        ]
    ]
];