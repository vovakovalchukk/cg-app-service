<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class TrackingUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'tracking';
    protected const COLUMNS = [
        [
            'carrier',
            'varchar(120)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'number',
            'varchar(120)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'shippingService',
            'varchar(120)',
            'utf8mb4_0900_ai_ci',
        ],
    ];

    public function up()
    {
        $this->onlineSchemaChange(
            static::TABLE,
            $this->buildModifyColumnCollateStatement(static::COLUMNS)
        );
    }

    public function down()
    {
        // we don't want to reverse this
        return;
    }
}
