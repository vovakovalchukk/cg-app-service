<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class UnimportedListingUtf8Columns extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLE = 'unimportedListing';
    protected const COLUMNS = [
        [
            'sku',
            'varchar(255)',
            'utf8mb4_0900_ai_ci',
        ],
        [
            'title',
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

    public function supportsEnvironment($environment)
    {
        return $environment === 'listings';
    }
}
