<?php

use Phinx\Migration\AbstractMigration;

class LinkOrderUniqueIndex extends AbstractMigration
{
    const TABLE_ORDER_LINK_ORDER = 'orderLinkOrders';

    public function up()
    {
        $this
            ->table(static::TABLE_ORDER_LINK_ORDER)
            ->removeIndex(['orderId'])
            ->update();

        $this->execute("ALTER IGNORE TABLE `orderLinkOrders` ADD UNIQUE (`orderId`);");
    }

    public function down()
    {
        $this
            ->table(static::TABLE_ORDER_LINK_ORDER)
            ->removeIndex(['orderId'])
            ->addIndex(['orderId'])
            ->update();
    }
}
