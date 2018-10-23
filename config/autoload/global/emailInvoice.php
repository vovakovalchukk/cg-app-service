<?php
use CG\Order\Client\Gearman\Generator\AutoEmailInvoice as AutoEmailInvoiceGenerator;

return [
    'di' => [
        'instance' => [
            AutoEmailInvoiceGenerator::class => [
                'parameters' => [
                    'orderGearmanClient' => 'orderGearmanClient',
                ]
            ],
        ]
    ]
];