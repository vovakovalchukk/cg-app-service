<?php

use Phinx\Migration\AbstractMigration;

class AddIndexesToCancelTables extends AbstractMigration
{
    const TABLE_CANCEL = 'cancel';
    const TABLE_CANCEL_ITEM = 'cancelItem';

    public function up()
    {
        $this->table(self::TABLE_CANCEL)
            ->addIndex(['orderId'])
            ->update();
        $this->table(self::TABLE_CANCEL_ITEM)
            ->addIndex(['cancelId'])
            ->addIndex(['orderItemId', 'orderId'], ['unique' => true])
            ->update();
    }

    public function down()
    {
        $this->table(self::TABLE_CANCEL)
            ->removeIndex(['orderId'])
            ->update();
        $this->table(self::TABLE_CANCEL_ITEM)
            ->removeIndex(['cancelId'])
            ->removeIndex(['orderItemId', 'orderId'])
            ->update();
    }
}