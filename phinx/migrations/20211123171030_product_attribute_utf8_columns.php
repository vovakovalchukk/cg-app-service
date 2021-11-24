<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ProductAttributeUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'productAttribute';
    protected const COLUMNS = [
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
