<?php
use Slim\Slim;
use CG\Slim\Versioning\Version;
use Zend\Di\Di;

use CG\Controllers\Root;

//Tracking
use CG\Controllers\Tracking\Collection as TrackingCollection;
use CG\InputValidation\Tracking\Filter as TrackingFilterValidationRules;

//UserPreference
use CG\Controllers\UserPreference\UserPreference;
use CG\Controllers\UserPreference\UserPreference\Collection as UserPreferenceCollection;
use CG\InputValidation\UserPreference\Entity as UserPreferenceEntityValidationRules;
use CG\Slim\InputValidation\PageLimit as UserPreferenceFilterValidationRules;

//Tag
use CG\Controllers\Order\Tag;
use CG\Controllers\Order\Tag\Collection as TagCollection;
use CG\InputValidation\Order\Tag\Entity as TagEntityValidationRules;
use CG\InputValidation\Order\Tag\Filter as TagFilterValidationRules;

//Filter
use CG\Controllers\Order\Filter;
use CG\Controllers\Order\Filter\Collection as FilterCollection;

// Label
use CG\Controllers\Order\Label as LabelController;
use CG\Controllers\Order\Label\Collection as LabelCollectionController;
use CG\InputValidation\Order\Label\Entity as LabelEntityValidationRules;
use CG\InputValidation\Order\Label\Filter as LabelFilterValidationRules;
use CG\Order\Shared\Label\Entity as LabelEntity;
use CG\Order\Shared\Label\Mapper as LabelMapper;
use CG\Order\Service\Label\Service as LabelService;

//ShippingMethod
use CG\Controllers\Shipping\Method\Method as ShippingMethod;
use CG\Controllers\Shipping\Method\Method\Collection as ShippingMethodCollection;
use CG\InputValidation\Shipping\Method\Filter as ShippingMethodFilterValidationRules;

/** @var Di $di */
/** @var Slim $app */
return array(
    '/' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Root::class);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'Root'
    ),
    '/userPreference' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(UserPreferenceCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'entityRoute' => '/userPreference/:userId',
        'name' => 'UserPreferenceCollection',
        'validation' => array("dataRules" => null, "filterRules" => UserPreferenceFilterValidationRules::class, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/userPreference/:userId' => array (
        'controllers' => function($userId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(UserPreference::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($userId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'UserPreferenceEntity',
        'validation' => array("dataRules" => UserPreferenceEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderTag' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(TagCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method()
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'entityRoute' => '/orderTag/:tagId',
        'name' => 'TagCollection',
        'validation' => array("dataRules" => null, "filterRules" => TagFilterValidationRules::class, "flatten" => false)
    ),
    '/orderTag/:tagId' => array (
        'controllers' => function($tagId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Tag::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($tagId, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'TagEntity',
        'validation' => array("dataRules" => TagEntityValidationRules::class, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderFilter' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(FilterCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => array('POST', 'OPTIONS'),
        'entityRoute' => '/orderFilter/:filterId',
        'name' => 'FilterCollection',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false)
    ),
    '/orderFilter/:filterId' => array (
        'controllers' => function($filterId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Filter::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($filterId)
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'FilterEntity',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false),
        'eTag' => [
            'enabled' => false
        ]
    ),
    '/orderLabel' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(LabelCollectionController::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'entityRoute' => '/orderLabel/:labelId',
        'name' => 'OrderLabelCollection',
        'version' => new Version(1, 7),
        'validation' => ["dataRules" => LabelEntityValidationRules::class, "filterRules" => LabelFilterValidationRules::class, "flatten" => false]
    ],
    '/orderLabel/:labelId' => [
        'controllers' => function($labelId) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(LabelController::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($labelId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'OrderLabelEntity',
        'version' => new Version(1, 7),
        'validation' => ["dataRules" => LabelEntityValidationRules::class, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'mapperClass' => LabelMapper::class,
            'entityClass' => LabelEntity::class,
            'serviceClass' => LabelService::class
        ]
    ],
    '/shippingMethod' => [
        'controllers' => function() use ($app, $di) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ShippingMethodCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['POST', 'GET', 'OPTIONS'],
        'entityRoute' => '/shippingMethod/:id',
        'name' => 'ShippingMethodCollection',
        'validation' => [
            "filterRules" => ShippingMethodFilterValidationRules::class,
            "flatten" => false
        ],
    ],
    '/shippingMethod/:id' => [
        'controllers' => function($shippingMethodId) use ($app, $di) {
                $method = $app->request()->getMethod();

                $controller = $di->get(ShippingMethod::class, []);
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($shippingMethodId, $app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'ShippingMethodEntity',
        'validation' => ["dataRules" => null, "filterRules" => null, "flatten" => false],
        'eTag' => [
            'enabled' => false
        ]
    ],
    '/tracking' => [
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(TrackingCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => ['GET', 'OPTIONS'],
        'name' => 'TrackingCollection',
        'validation' => [
            "filterRules" => TrackingFilterValidationRules::class,
        ],
        'version' => new Version(1, 4),
    ],
);
