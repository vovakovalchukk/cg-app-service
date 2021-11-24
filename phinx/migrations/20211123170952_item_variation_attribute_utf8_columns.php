<?php

use Phinx\Migration\AbstractOnlineSchemaChange;

class ItemVariationAttributeUtf8Columns extends AbstractOnlineSchemaChange
{
    protected const TABLE = 'itemVariationAttribute';
    protected const COLUMNS = [

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
