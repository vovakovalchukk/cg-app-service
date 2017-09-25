<?php

use CG\Controllers\Ekm\Ekm as EkmController;
use CG\Controllers\Ekm\Registration\Entity as Registration;
use CG\Controllers\Ekm\Registration\Collection as RegistrationCollection;
use CG\Ekm\Registration\Entity as RegistrationEntity;
use CG\Ekm\Registration\Mapper as RegistrationMapper;
use CG\Ekm\Registration\Service as RegistrationService;
use CG\InputValidation\Ekm\Registration\Entity as RegistrationEntityValidationRules;
use CG\InputValidation\Ekm\Registration\Filter as RegistrationFilterValidationRules;

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
    '/ekm/registration/:registrationId' => [
        'controllers' => function($registrationId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(Registration::class, []);
            $app->view()->set(
                'RestResponse',
                $controller->$method($registrationId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'RegistrationEntity',
        'validation' => ["dataRules" => RegistrationEntityValidationRules::class, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'mapperClass' => RegistrationMapper::class,
            'entityClass' => RegistrationEntity::class,
            'serviceClass' => RegistrationService::class
        ]
    ],
];