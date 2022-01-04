<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class StockAdjustmentLogUtf8Columns extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    protected const TABLE = 'stockAdjustmentLog';

    public function up()
    {
        $this->onlineSchemaChange(
            static::TABLE,
            'ADD UNIQUE INDEX IdStid (id, stid), ' .
            'MODIFY COLUMN `sku` varchar(255) COLLATE utf8mb4_0900_ai_ci NOT NULL, ' .
            'MODIFY COLUMN `referenceSku` varchar(255) COLLATE utf8mb4_0900_ai_ci DEFAULT NULL, ' .
            'MODIFY COLUMN `itemStatus` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL, ' .
            'MODIFY COLUMN `listingStatus` varchar(50) COLLATE utf8mb4_0900_ai_ci NOT NULL'
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

    protected function getAdditionalArguments(): array
    {
        // Note: this flag is dangerous. It will silently drop any duplicates.
        // We have checked the table for this particular change and believe it is fine.
        // A unique key is already in place in the Amazon RDS archive version of this table
        return ['--no-check-unique-key-change'];
    }
}
