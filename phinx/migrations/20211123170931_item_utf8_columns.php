<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ItemUtf8Columns extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLE = 'item';
    protected const COLUMNS = [
        [
            'itemName',
            'longtext',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'itemSku',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'customisation',
            'mediumtext',
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

    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }
}
