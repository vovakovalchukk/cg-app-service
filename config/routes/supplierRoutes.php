<?php
use CG\Controllers\Supplier\Collection as SupplierCollectionController;
use CG\Controllers\Supplier\Entity as SupplierController;
use CG\Supplier\Entity as Supplier;
use CG\Supplier\Mapper as SupplierMapper;
use CG\Supplier\Service as SupplierService;
use CG\InputValidation\Supplier\Entity as SupplierEntityValidation;
use CG\InputValidation\Supplier\Filter as SupplierCollectionValidation;

return [
    '/supplier' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(SupplierCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'SupplierCollection',
        'entityRoute' => '/supplier/:id',
        'validation' => [
            'filterRules' => SupplierCollectionValidation::class,
            'dataRules' => SupplierEntityValidation::class
        ]
    ],
    '/supplier/:id' => [
        'controllers' => function($supplierId) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(SupplierController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($supplierId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'SupplierEntity',
        'validation' => [
            "dataRules" => SupplierEntityValidation::class
        ],
        'eTag' => [
            'mapperClass' => SupplierMapper::class,
            'entityClass' => Supplier::class,
            'serviceClass' => SupplierService::class
        ],
    ]
];