<?php

use CG\Controllers\Reporting\Order\Order as ReportOrder;
use CG\InputValidation\Reporting\Order\Filter as OrderFilterValidation;

return [
    '/report/order/:dimension' => [
        'controllers' => function($dimension) use ($app, $di) {
            $method = $app->request()->getMethod();
            $controller = $di->get(ReportOrder::class);
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
            "filterRules" => OrderFilterValidation::class
        ],
    ]
];
