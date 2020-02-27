<?php
use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddBarcodeNotApplicableToProductDetail extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $this->onlineSchemaChange('productDetail', 'ADD COLUMN `barcodeNotApplicable` TINYINT(1) NULL', 200);
    }

    public function down()
    {
        $this->onlineSchemaChange('productDetail', 'DROP COLUMN `barcodeNotApplicable`');
    }
}