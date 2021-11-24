<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class OrderTagUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'orderTag';
    protected const COLUMNS = [
        [
            'sku',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'name',
            'varchar(255)',
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
