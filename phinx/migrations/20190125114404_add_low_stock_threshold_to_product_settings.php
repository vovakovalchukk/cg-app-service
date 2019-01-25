<?php

use Phinx\Migration\AbstractMigration;

class AddLowStockThresholdToProductSettings extends AbstractMigration
{
    public function change()
    {
        $this->table('productSettings')
            ->addColumn('lowStockThresholdOn', 'boolean', ['null' => true])
            ->addColumn('lowStockThresholdValue', 'integer', ['length' => 10, 'null' => true])
            ->update();
    }
}
