<?php

use CG\Controllers\Report\Order\Collection as ReportOrderCollection;

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
        'name' => 'ReportOrderCollection'
    ]
];
