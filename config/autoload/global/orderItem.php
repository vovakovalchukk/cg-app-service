<?php
use CG\Order\Client\Gearman\Generator\UpdateItemsSupplier as UpdateItemsSupplierGenerator;

return [
    'di' => [
        'instance' => [
            UpdateItemsSupplierGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'orderGearmanClient',
                ]
            ],
        ]
    ]
];