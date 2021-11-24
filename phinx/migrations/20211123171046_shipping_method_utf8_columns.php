<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ShippingMethodUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'shippingMethod';
    protected const COLUMNS = [
        [
            'method',
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
