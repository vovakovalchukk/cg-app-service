<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddGtinToProductDetail extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
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
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `gtin` VARCHAR(14) NULL', 200);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `gtin`');
    }
}