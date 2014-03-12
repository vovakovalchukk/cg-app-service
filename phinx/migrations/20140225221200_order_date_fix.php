<?php
use Phinx\Migration\AbstractMigration;

class OrderDateFix extends AbstractMigration
{
    public function up()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('purchaseDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('paymentDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('printedDate', 'datetime', array('null' => true));
        $orderTable->changeColumn('dispatchDate', 'datetime', array('null' => true));
    }

    public function down()
    {
        $orderTable = $this->table('order');
        $orderTable->changeColumn('purchaseDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('paymentDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('printedDate', 'datetime', array('null' => false));
        $orderTable->changeColumn('dispatchDate', 'datetime', array('null' => false));
    }
}