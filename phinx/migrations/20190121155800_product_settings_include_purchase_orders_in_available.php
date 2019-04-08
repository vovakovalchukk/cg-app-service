<?php

use Phinx\Migration\AbstractMigration;

class ProductSettingsIncludePurchaseOrdersInAvailable extends AbstractMigration
{
    public function change()
    {
        $this->table('productSettings')
            ->addColumn('includePurchaseOrdersInAvailable', 'boolean', ['default' => 0])
            ->update();
    }
}
