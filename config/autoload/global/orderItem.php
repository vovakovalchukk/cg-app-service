<?php
use CG\Order\Client\Gearman\Generator\UpdateItemsSupplier as UpdateItemsSupplierGenerator;
use CG\Order\Command\UpdateItemsWithSuppliers;

return [
    'di' => [
        'instance' => [
            UpdateItemsSupplierGenerator::class => [
                'parameters' => [
                    'gearmanClient' => 'orderGearmanClient',
                ]
            ],
            UpdateItemsWithSuppliers::class => [
                'parameters' => [
                    'readSql' => 'ReadSql'
                ]
            ]
        ]
    ]
];