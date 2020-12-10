<?php
use CG\Amazon\Gearman\Generator\UploadInvoiceForOrder as UploadInvoiceForOrderGenerator;
return [
    'di' => [
        'instance' => [
            UploadInvoiceForOrderGenerator::class => [
                'parameters' => [
                    'amazonGearmanClient' => 'amazonGearmanClient',
                ]
            ],
        ]
    ]
];