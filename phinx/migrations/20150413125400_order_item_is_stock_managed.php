<?php
use Phinx\Migration\AbstractMigration;

use CG\Order\Shared\Item\Entity as Item;

class OrderItemIsStockManaged extends AbstractMigration
{
    const TABLE_NAME = 'item';

    public function change()
    {
        $this->table(static::TABLE_NAME)
            ->addColumn('isStockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }
}
