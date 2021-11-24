<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLES = [
        'order',
        'orderLive',
    ];
    protected const COLUMNS = [
        [
            'shippingMethod',
            'text',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'discountDescription',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'paymentMethod',
            'varchar(120)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'paymentReference',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'externalUsername',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'recipientVatNumber',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'iossNumber',
            'varchar(25)',
            'utf8mb4_0900_ai_ci',
        ],
    ];

    public function up()
    {
        foreach (static::TABLES as $table) {
            $this->onlineSchemaChange(
                $table,
                $this->buildModifyColumnCollateStatement(static::COLUMNS)
            );
        }
    }

    public function down()
    {
        // we don't want to reverse this
        return;
    }
}
