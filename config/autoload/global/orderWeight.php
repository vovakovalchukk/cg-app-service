<?php
use CG\Order\Client\Gearman\Generator\CalculateOrderWeight as OrderWeightCalculatorGenerator;

return [
    'di' => [
        'instance' => [
            OrderWeightCalculatorGenerator::class => [
                'parameters' => [
                    'orderGearmanClient' => 'orderGearmanClient',
                ]
            ],
        ]
    ]
];