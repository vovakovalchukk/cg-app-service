<?php

use Phinx\Migration\AbstractMigration;

class DiscountDescription extends AbstractMigration
{
    public function change()
    {
        $table = $this->table('order');
        $table->addColumn(
            'discountDescription',
            'string', [
                'after' => 'totalDiscount',
                'length' => 255,
                'null' => true
            ])->update();
    }
}
