<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class GiftWrapUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'giftWrap';
    protected const COLUMNS = [
        [
            'giftWrapType',
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
