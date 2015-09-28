<?php
use CG\Stock\Command\ProcessAudit as ProcessStockAuditCommand;

return [
    'di' => [
        'instance' => [
            ProcessStockAuditCommand::class => [
                'parameters' => [
                    'predisClient' => 'audit_redis',
                ]
            ]
        ]
    ]
];
