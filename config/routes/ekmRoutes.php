<?php

return [
    '/ekm' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(EkmController::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'Settings'
    ],
    '/ekm/registration' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(RegistrationCollection::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/ekm/registration/:ekmRegistrationId',
        'name' => 'RegistrationCollection',
        'validation' => ["dataRules" => RegistrationEntityValidationRules::class, "filterRules" => RegistrationFilterValidationRules::class, "flatten" => false]
    ],
    '/ekm/registration/:ekmRegistrationId' => [
        'controllers' => function($packageRulesId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(Registration::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($packageRulesId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'PackageRulesEntity',
        'validation' => ["dataRules" => RegistrationEntityValidationRules::class, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'mapperClass' => RegistrationMapper::class,
            'entityClass' => RegistrationEntity::class,
            'serviceClass' => RegistrationService::class
        ]
    ],
];