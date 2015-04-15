<?php
use Phinx\Migration\AbstractMigration;

use CG\Order\Shared\Item\Entity as Item;

class OrderItemRenameToStockManaged extends AbstractMigration
{
    const TABLE_NAME = 'item';

    public function up()
    {
        $this->table(static::TABLE_NAME)
            ->removeColumn('isStockManaged')
            ->addColumn('stockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }

    public function down()
    {
        $this->table(static::TABLE_NAME)
            ->removeColumn('stockManaged')
            ->addColumn('isStockManaged', 'boolean', ['default' => Item::DEFAULT_IS_STOCK_MANAGED])
            ->update();
    }
}
