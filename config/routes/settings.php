<?php

use Slim\Slim;

// Settings
use CG\Controllers\Settings\Root;

// Invoice
use CG\Controllers\Settings\Invoice\Collection as InvoiceCollection;
use CG\Controllers\Settings\Invoice;


return array(
    '/settings' => array (
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
        'name' => 'SettingsRoot'
    ),
    '/settings/invoice' => array (
        'controllers' => function() use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();

                $controller = $di->get(InvoiceCollection::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($app->request()->getBody())
                );
            },
        'via' => array('GET', 'OPTIONS'),
        'name' => 'ServiceCollection',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false)
    ),
    '/settings/invoice/:id' => array (
        'controllers' => function($id) use ($di) {
                $app = $di->get(Slim::class);
                $method = $app->request()->getMethod();
                $controller = $di->get(Invoice::class, array());
                $app->view()->set(
                    'RestResponse',
                    $controller->$method($id, $app->request()->getBody())
                );
            },
        'via' => array('GET', 'PUT', 'DELETE', 'OPTIONS'),
        'name' => 'ServiceEntity',
        'validation' => array("dataRules" => null, "filterRules" => null, "flatten" => false)
    )
);