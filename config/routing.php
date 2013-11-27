<?php
use Slim\Slim;
use CG\Controllers\Root;
use CG\Controllers\App\Service\Collection as ServiceCollection;
use CG\Controllers\App\Service as Service;
use CG\Controllers\App\Event\Collection as EventCollection;
use CG\Controllers\App\Event;
use CG\InputValidation\App\Service\Entity as ServiceEntityValidationRules;
use CG\InputValidation\App\Event\Entity as EventEntityValidationRules;

$routes = array(
    '/' => array (
        'controllers' => function() use ($serviceManager) {
                $di = $serviceManager->get('Di');
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
    '/service' => array (
        'controllers' => function() use ($serviceManager) {
                $di = $serviceManager->get('Di');
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(ServiceCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'name' => 'ServiceCollection',
        'validation' => array("dataRules" => ServiceEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/service/:id' => array (
        'controllers' => function($id) use ($serviceManager) {
                $di = $serviceManager->get('Di');
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(Service::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($id, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'ServiceEntity',
        'validation' => array("dataRules" => ServiceEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/service/:id/event' => array (
        'controllers' => function($id) use ($serviceManager) {
                $di = $serviceManager->get('Di');
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(EventCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($id, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'POST', 'OPTIONS'),
        'name' => 'ServiceEventEntity',
        'validation' => array("dataRules" => EventEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    ),
    '/service/:id/event/:eventType' => array (
        'controllers' => function($id, $eventType) use ($serviceManager) {
                $di = $serviceManager->get('Di');
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(Event::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($id, $eventType, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'ServiceEventEntity',
        'validation' => array("dataRules" => EventEntityValidationRules::class, "filterRules" => null, "flatten" => false)
    )
);
