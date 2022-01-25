<?php
use CG\Order\Client\Gearman\Generator\UpdateCustomerOrderCount as UpdateCustomerOrderCountGenerator;

return [
    'di' => [
        'instance' => [
            UpdateCustomerOrderCountGenerator::class => [
                'parameters' => [
                    'orderGearmanClient' => 'orderGearmanClient',
                ]
            ],
        ]
    ]
];