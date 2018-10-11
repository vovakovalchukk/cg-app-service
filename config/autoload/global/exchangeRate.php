<?php
use CG\Order\Client\Gearman\Generator\UpdateExchangeRate as ExchangeRateUpdaterGenerator;

return [
    'di' => [
        'instance' => [
            ExchangeRateUpdaterGenerator::class => [
                'parameters' => [
                    'orderGearmanClient' => 'orderGearmanClient',
                ]
            ],
        ]
    ]
];