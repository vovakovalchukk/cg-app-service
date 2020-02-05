<?php

use Phinx\Migration\AbstractOnlineSchemaChange;
use Phinx\Migration\EnvironmentAwareInterface;

class AddReorderQuantity extends AbstractOnlineSchemaChange implements EnvironmentAwareInterface
{
    public function supportsEnvironment($environment)
    {
        return $environment === 'cg_app';
    }

    public function up()
    {
        $productSettingsAlter = 'ADD COLUMN `reorderQuantity` INT(10) NULL DEFAULT 1';
        $stockAlter = 'ADD COLUMN `reorderQuantity` INT(10) NULL DEFAULT NULL';

        $this->onlineSchemaChange('productSettings', $productSettingsAlter, 200);
        $this->onlineSchemaChange('stock', $stockAlter, 200);
    }

    public function down()
    {
        $alter = 'DROP COLUMN `reorderQuantity`';

        $this->onlineSchemaChange('productSettings', $alter, 200);
        $this->onlineSchemaChange('stock', $alter, 200);
    }
}
