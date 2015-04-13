<?php
use Phinx\Migration\AbstractMigration;

class OrderItemIsStockManaged extends AbstractMigration
{
    const TABLE_NAME = 'item';

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('isStockManaged', 'boolean', ['default' => true])
            ->update();
    }
}
