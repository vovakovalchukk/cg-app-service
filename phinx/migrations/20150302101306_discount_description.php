<?php

use Phinx\Migration\AbstractMigration;

class DiscountDescription extends AbstractMigration
{
    public function change()
    {
        $this->updateTable('order');
        $this->updateTable('orderLive');
    }

    protected function updateTable($tableName)
    {
        $table = $this->table($tableName);
        $table->addColumn(
            'discountDescription',
            'string', [
            'after' => 'totalDiscount',
            'length' => 255,
            'null' => true
        ])->update();
    }
}
