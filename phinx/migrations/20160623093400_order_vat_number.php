<?php
use Phinx\Migration\AbstractMigration;

class OrderVatNumber extends AbstractMigration
{
    public function up()
    {
        $this
            ->table('orderInvoice')
            ->addColumn('vatNumber', 'string', ['null' => true])
            ->changeColumn('invoiceNumber', 'integer', ['null' => true])
            ->update();
    }

    public function down()
    {
        $this
            ->table('orderInvoice')
            ->removeColumn('vatNumber')
            ->changeColumn('invoiceNumber', 'integer', ['null' => false])
            ->update();
    }
}