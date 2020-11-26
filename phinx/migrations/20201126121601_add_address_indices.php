<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddAddressIndices extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        $this->onlineSchemaChange('address', implode(', ', [
            'ADD INDEX `addressCountry` (`addressCountry`)',
            'ADD INDEX `idx_address_addressCountryCode` (`addressCountryCode`)',
        ]), 200);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('stockAdjustmentLog', implode(', ', [
            'DROP COLUMN `referenceSku`',
            'DROP COLUMN `referenceQuantity`',
        ]), 200);
    }
}
