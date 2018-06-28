<?php

use Phinx\Migration\AbstractMigration;

class OrderLabelCost extends AbstractMigration
{
    public function change()
    {
        $this->table('orderLabel')
            ->addColumn('costPrice', 'decimal', ['precision' => 12, 'scale' => 4, 'null' => true])
            ->addColumn('costCurrencyCode', 'string', ['null' => true])
            ->update();
    }
}