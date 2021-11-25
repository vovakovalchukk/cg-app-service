<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class ListingStatusHistoryUtf8Columns extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLE = 'listingStatusHistory';
    protected const COLUMNS = [
        [
            'message',
            'longtext',
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
