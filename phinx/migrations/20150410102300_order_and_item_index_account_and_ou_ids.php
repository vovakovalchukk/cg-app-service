<?php
use Phinx\Migration\AbstractMigration;

class OrderAndItemIndexAccountAndOuIds extends AbstractMigration
{
    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable
            ->addIndex(['accountId', 'organisationUnitId'])
            ->save();

        $itemTable = $this->table('item');
        $itemTable
            ->addIndex(['accountId', 'organisationUnitId'])
            ->addIndex('organisationUnitId')
            ->save();
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable
            ->removeIndex(['accountId', 'organisationUnitId'])
            ->save();

        $itemTable = $this->table('item');
        $itemTable
            ->removeIndex(['accountId', 'organisationUnitId'])
            ->removeIndex('organisationUnitId')
            ->save();
    }
}