<?php
use Slim\Slim;
use CG\Slim\Versioning\Version;
use CG\Controllers\Template\Template;
use CG\Controllers\Template\Template\Collection as TemplateCollection;
use CG\InputValidation\Template\Entity as TemplateEntityValidationRules;
use CG\InputValidation\Template\Filter as TemplateFilterValidationRules;

use CG\Template\Entity as TemplateEntity;
use CG\Template\Mapper as TemplateMapper;
use CG\Template\Service as TemplateService;

return [
    '/template' => [
        'controllers' => function() use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(TemplateCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'TemplateCollection',
        'entityRoute' => '/template/:id',
        'validation' => [
            "dataRules" => TemplateEntityValidationRules::class,
            "filterRules" => TemplateFilterValidationRules::class,
            "flatten" => false
        ],
        'version' => new Version(1, 4)
    ],
    '/template/:id' => [
        'controllers' => function($templateId) use ($di) {
            $app = $di->get(Slim::class);
            $method = $app->request()->getMethod();

            $controller = $di->get(Template::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($templateId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'DELETE', 'PUT', 'OPTIONS'],
        'name' => 'TemplateEntity',
        'validation' => [
            "dataRules" => TemplateEntityValidationRules::class,
            "filterRules" => null,
            "flatten" => false
        ],
        'version' => new Version(1, 4),
        'eTag' => [
            'mapperClass' => TemplateMapper::class,
            'entityClass' => TemplateEntity::class,
            'serviceClass' => TemplateService::class
        ]
    ],
];