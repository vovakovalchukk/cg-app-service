<?php

use CG\Controllers\Reporting\Order\Collection as ReportOrderCollection;
use CG\InputValidation\Reporting\Order\Filter as OrderFilterValdiation;

return [
    '/report/order/:dimension' => [
        'controllers' => function($dimension) use ($app, $di) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ReportOrderCollection::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($dimension)
            );
        },
        'via' => ['GET'],
        'name' => 'ReportOrderCollection',
        'validation' => [
            "flatten" => false,
            "dataRules" => null,
            "filterRules" => OrderFilterValdiation::class
        ],
    ]
];
